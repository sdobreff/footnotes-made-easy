;(function($, undefined){
	
	var timeout,
		timing = 300,
		$identifiers,
		selector = 'a.footnote-identifier-link';
	
	$(function() {
		
		$identifiers = $(selector);
		
		$identifiers.tooltip({
			tooltipClass: "footnote footnote-tooltip",
			content: function() {
				var element = $( this );
				var bibentry = element.attr("href");
				return $(bibentry).html();
			}
		}).on('mouseleave focusout', function(event){
			
			// Stop jQuery UI tooltips widget getting the signal to close the tooltip.
			// Note: this is problematic as it may interfere with other JS acting on these events.
			event.stopImmediatePropagation();
			timeout = setTimeout(function() {$identifiers.tooltip("close")}, timing); 
			
		}).on('mouseover focusin', function(event){
			clearTimeout(timeout);
		});
		
		// Stop the tooltip from closing while mouse is hovered.
		$(document).on('mouseover', '.ui-tooltip', function(){
			clearTimeout(timeout);
		});
		
		// Close the tooltip when mouse leaves tooltip.
		$(document).on('mouseleave', '.ui-tooltip', function(){
			clearTimeout(timeout);
			timeout = setTimeout(function() {$identifiers.tooltip("close")}, timing); 
		});
	});
	
}(jQuery));
