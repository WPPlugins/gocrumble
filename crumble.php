<?php
/**
 * Plugin Name: Crumble
 * Plugin URI:  http://gocrumble.com
 * Description: Crumble guest login for Wordpress
 * Version:     1.0.0
 * Author:      gocrumble
 * Author URI:  http://mindmutex.com
 * Donate link: http://gocrumble.com
 * License:     GPLv2
 * Text Domain: crumble
 * Domain Path: /languages
 *
 * @link http://gocrumble.com
 *
 * @package Crumble
 * @version 1.0.0
 */

/**
 * Copyright (c) 2017 mindmutex (email : team@gocrumble.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

final class Crumble {
  const VERSION = '1.0.0';

  protected $url = '';
  protected $path = '';
  protected $basename = '';

  protected static $single_instance = null;

  public static function get_instance() {
    if (null === self::$single_instance) {
      self::$single_instance = new self();
    }
    return self::$single_instance;
  }

  protected function __construct() {
    $this->basename = plugin_basename( __FILE__ );
    $this->url = plugin_dir_url( __FILE__ );
    $this->path = plugin_dir_path( __FILE__ );
  }

  public function plugin_classes() {
    require(self::dir("vendor/autoload.php"));
    require(self::dir("includes/contact-widget.php"));

  }

  public function hooks() {
    add_action("init", array($this, "init"), 0);
  }

  public function _activate() {
    flush_rewrite_rules();
  }

  public function _deactivate() {
  }

  public function init() {
    load_plugin_textdomain("crumble", false, dirname($this->basename) . "/languages/");

    wp_enqueue_script("crumble-widget", self::url("assets/js/crumble.js"), ["jquery"]);

    wp_enqueue_style("crumble-css", self::url("assets/css/crumble.css"));
    wp_enqueue_style("font-awesome",
        self::url("bower_components/font-awesome/css/font-awesome.min.css"));

    if (!session_id()) {
      session_start();
    }
    $this->plugin_classes();
  }

  public function deactivate_me() {
    if (function_exists("deactivate_plugins")) {
      deactivate_plugins($this->basename);
    }
  }

  public function __get( $field ) {
    switch ( $field ) {
      case 'version':
        return self::VERSION;
      case 'basename':
      case 'url':
      case 'path':
        return $this->$field;
      default:
        throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
    }
  }

  public static function include_file($filename) {
    $file = self::dir($filename . ".php");
    if (file_exists($file)) {
      return include_once($file);
    }
    return false;
  }

  public static function dir($path = "") {
    static $dir;
    $dir = $dir ? $dir : trailingslashit(dirname(__FILE__));
    return $dir . $path;
  }

  public static function url($path = "") {
    static $url;
    $url = $url ? $url : trailingslashit(plugin_dir_url(__FILE__));
    return $url . $path;
  }
}

function crumble() {
  return Crumble::get_instance();
}

function crumble_render_view($name, $model) {
  if (!preg_match("/^[\w_]+$/", $name)) {
    return "Unknown template";
  }
  $path = Crumble::dir("templates/" . $name . ".php");
  if (!file_exists($path)) {
    return "Unknown template";
  }
  if (is_string($model) && is_callable($model)) {
    $model = call_user_func_array($model);
  }

  ob_start();
  if (is_array($model)) {
    foreach ($model as $key => $value) {
      ${$key} = $value;
    }
  }
  include($path);
  $result = ob_get_contents();
  ob_end_clean();

  return $result;
}

add_action("plugins_loaded", array(crumble(), "hooks"));

register_activation_hook(__FILE__, array(crumble(), "_activate"));
register_deactivation_hook( __FILE__, array(crumble(), "_deactivate"));
