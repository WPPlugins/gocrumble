<?php
/**
 * Crumble Contact Widget
 *
 * @since 1.0.0
 * @package Crumble
 */

use Crumble\Client;

function _action($name, $handler) {
  add_action("wp_ajax_" . $name, $handler);
  add_action("wp_ajax_nopriv_" . $name, $handler);
}

class Crumble_Contact_Widget extends WP_Widget {
  protected $widget_slug = "crumble-contact-widget";
  protected $widget_name = "";
  protected $default_widget_title = "";
  protected $default_widget_endpoint = "https://clients.gocrumble.com";

  protected $client;

  protected static $shortcode = 'crumble-contact-widget';

  public function __construct() {
    $this->widget_name = esc_html__("Crumble Contact Area", "crumble");
    $this->default_widget_title = esc_html__("Crumble Contact Area", "crumble");

    parent::__construct($this->widget_slug, $this->widget_name, [
      "classname" => $this->widget_slug,
      "description" => esc_html__("Please use your credentials to login", "crumble"),
    ]);

    add_action("switch_theme", [$this, "flush_widget_cache"]);
    add_shortcode(self::$shortcode, [__CLASS__, "get_widget"]);

    // ajax actions
    _action("crumble_login", [__CLASS__, "handle_login_action"]);
    _action("crumble_logout", [__CLASS__, "handle_logout_action"]);

    _action("crumble_view",  [__CLASS__, "handle_view"]);
  }

  /**
   * Retrieve the widget options.
   *
   * @param unknown $widget widget
   * @return unknown array with options
   */
  public static function options($widget) {
    $index = strrpos($widget, "-");
    if ($index == -1) {
      die(http_response_code(400));
    }

    // the widget options can be stored in session or database
    // short code widget options are stored in session
    if (!empty($_SESSION[$widget])) {
      return (array) $_SESSION[$widget];
    }

    // otherwise lookup in database
    $options = get_option("widget_" .  substr($widget, 0, $index));
    if (empty($options)) {
      die(http_response_code(400));
    }
    return $options[intval(substr($widget, $index + 1, strlen($widget)))];
  }

  /**
   * Create the Crumble API client.
   *
   * @param unknown $options widget options
   * @param unknown $username username to obtain token
   * @param unknown $password password to authenticate username
   *
   * @return \Crumble\Client client
   */
  public static function build_client($options, $username = NULL, $password = NULL) {
    $endpoint = $options["endpoint"] . "/api";
    $endpoint_details = parse_url($options["endpoint"]);

    $params = ["client_id" => "crumble-wp-client"];

    if (!empty($username) && !empty($password)) {
      $params["username"] = $endpoint_details["host"] . "/" . $username;
      $params["password"] = $password;
    }
    $client = new Client($endpoint, "contact", $params);
    $client->getTokenStore()->setParamName("crumble:" . $endpoint_details["host"]);

    return $client;
  }

  /**
   * Render a view.
   *
   * @param unknown $widget widget
   * @param unknown $view view
   */
  public static function build_view($widget, $view) {
    if (empty($widget) || empty($view) || !preg_match("/^[a-zA-Z_]+$/", $view)) {
      http_response_code(400);
    }
    $options = self::options($widget);
    $client = self::build_client($options);

    $model = [
      "client" => $client,
      "options" => $options
    ];
    // attempt to call function that would build the model for view
    $fn = [__CLASS__, "handle_" . $view . "_model"];
    if (is_callable($fn)) {
      $model = array_merge($model, call_user_func($fn, $model));
    }
    return crumble_render_view($view, $model);
  }

  /**
   * General handler to serve views via ajax.
   */
  public function handle_view() {
    die(self::build_view($_GET['widget'], $_GET['view']));
  }

  /**
   * Handle login form submit.
   *
   * Attempt to get token and if successful return 200,
   * otherwise return 400 - bad request
   */
  public function handle_login_action() {
    if (empty($_POST["crumble_email"]) || empty($_POST["crumble_password"]) || empty($_GET['widget'])) {
      die(http_response_code(400));
    } else {
      $options = self::options($_GET['widget']);
      $client = self::build_client($options, $_POST["crumble_email"], $_POST["crumble_password"]);

      $response = $client->obtainAccessTokenIfRequried();
      if ($response != NULL && $response->meta->status != 200) {
        die(http_response_code(400));
      }
      die(http_response_code(200));
    }
  }

  /**
   * Handle the logout.
   */
  public function handle_logout_action() {
    if (empty($_GET['widget'])) {
      die(http_response_code(400));
    }
    $options = self::options($_GET['widget']);

    $client = self::build_client($options);
    $client->getTokenStore()->delete();
  }

  /**
   * Handle the model for tokens view.
   *
   * @param array $model model
   * @return array model
   */
  public function handle_tokens_model($model) {
    $client = $model['client'];
    $tokens = $client->Entity("Token")->list()->data;

    usort($tokens, function($a, $b) {
      if ($a->entity->contentType == $b->entity->contentType) {
        return $a->entity->dateModified > $b->entity->dateModified;
      }
      return $a->entity->contentType != "@directory";
    });

    $model['tokens'] = $tokens;
    $model['contact'] = $client->Entity("Contact")->me()->data;

    return $model;
  }

  // -- ADMIN

  public function flush_widget_cache() {
    wp_cache_delete( $this->widget_slug, 'widget' );
  }

  public function widget( $args, $instance ) {
    echo self::get_widget([
      "before_widget" => $args["before_widget"],
      "after_widget" => $args["after_widget"],
      "before_title" => $args["before_title"],
      "after_title" => $args["after_title"],
      "title" => $instance["title"],
      "text" => $instance["text"],
      "widget_id" => $args["widget_id"],
    ]);
  }

  public static function get_widget($atts) {
    if (empty($atts['widget_id'])) {
      global $_SESSION;

      $widgetOptions = (object) $atts;

      // generated from the shortcode
      // create a random widget and store widget properties in session
      if (empty($widgetOptions->endpoint)) {
        return "shortcode is required to provide endpoint";
      }
      // store a random widget options in the sesssion
      $widget_id = "crumble-" . spl_object_hash($widgetOptions);

      $_SESSION[$widget_id] = $widgetOptions;
      $atts['widget_id'] = $widget_id;
    }

    $widget = '';
    // Set up default values for attributes.
    $atts = shortcode_atts([
        "before_widget" => "",
        "after_widget" => "",
        "before_title" => "",
        "after_title" => "",
        "title" => "",
        "text" => "",
        "widget_id" => "",
       ],
      (array) $atts,
      self::$shortcode);

    // Before widget hook.
    $widget .= $atts["before_widget"];
    $widget .= ($atts["title"]) ? $atts["before_title"] . esc_html($atts["title"]) . $atts["after_title"] : "";
    $widget .= wpautop(wp_kses_post($atts["text"]));

    $widget .= '<div class="crumble-container" data-ajax="' . admin_url("admin-ajax.php") . '" data-widget="' . $atts["widget_id"] . '">';

    $client = self::build_client(self::options($atts["widget_id"]));
    if ($client->isAuthenticated()) {
      $widget .= self::build_view($atts["widget_id"], "tokens");
    } else {
      $widget .= self::build_view($atts["widget_id"], "login");
    }
    $widget .= '</div>';

    // After widget hook.
    $widget .= $atts["after_widget"];
    return $widget;
  }

  public function update($new_instance, $old_instance) {
    $instance = $old_instance;

    $instance["title"] = sanitize_text_field($new_instance["title"]);
    $instance["endpoint"] = sanitize_text_field($new_instance["endpoint"]);

    if (current_user_can("unfiltered_html")) {
      $instance["text"] = force_balance_tags($new_instance["text"] );
    } else {
      $instance["text"] = stripslashes(wp_filter_post_kses(addslashes($new_instance["text"])));
    }
    $this->flush_widget_cache();
    return $instance;
  }

  public function form($instance) {
    $instance = wp_parse_args((array) $instance, [
        "title" => $this->default_widget_title,
        "text"  => "",
        "endpoint" => $this->default_widget_endpoint
    ]);
    echo crumble_render_view("form", ["widget" => $this, "instance" => $instance]);
  }
}

function crumble_register_contact_widget() {
  register_widget("Crumble_Contact_Widget");
}
add_action("widgets_init", "crumble_register_contact_widget");
