(function($){
  function url(element, options) {
    var root = element.parents('.crumble-container');
    var params = [
      'widget=' + root.data('widget')
    ];
   $.each(options, function(key, value) {
     params.push(key + '=' + value);
   })
   return root.data('ajax') + '?' + params.join('&'); 
  }

  $(function() {
  	// simple handler to submit and load view
  	var container = $('.crumble-container');
  	container.on('click', '[data-trigger]', function(e){
  		e.preventDefault();
  		
  		var element = $(this), action = element.data('trigger');
  		$.post(url(element, {
  		    action: action
  		  }))
  		  .success(function(){
  		    var successView = element.data("successView");
  		    element.parents('.crumble-container').load(url(element, {
  		      action: 'crumble_view',
  		      view: successView
  		    }));
  		  });
  	})
  	
  	// handle for login form
  	container.on('submit', '.crumble-contact-login', function(e) {
  	  e.preventDefault();
  	  
  	  var element = $(this), root = element.parents('.crumble-container');
  	  $.post(url(element), element.serializeArray())
        .success(function(){
   	      root.load(url(element, {
   	        action: 'crumble_view', view: "tokens"
   	      }));
        })
        .error(function() {
          root.find('.crumble-error').removeClass('hidden');
        });
  	});
  });

	
})(jQuery);
