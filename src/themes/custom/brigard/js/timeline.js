jQuery(document).ready(function(){
  	var ventana_alto = jQuery(window).height();
	var ventana_ancho = jQuery(window).width();
	
	/*linea tiempo escritorio*/
	var altoLine = jQuery('.dates-container .items-line').each(function(){
		var altoFinal = jQuery(this).outerHeight();
		var line_dates = jQuery(this).parent().find('.items-line ul li:last-child').outerHeight();
		var heightF = altoFinal - line_dates;
		jQuery(this).parent().find('.line_white').css({
			height : heightF
		});
	})

	var altoLine5 = jQuery('#linea5 .dates-container.en .items-line').each(function(){
		var altoFinal = jQuery(this).outerHeight();
		var line_dates = jQuery(this).parent().find('.items-line ul li:last-child').outerHeight() - 25;
		var heightF = altoFinal - line_dates;
		jQuery(this).parent().find('.line_white').css({
			height : heightF
		});
	})
	

	jQuery('.slick-experience').slick({
	  slidesToShow: 1
	});
});

jQuery(window).resize(function(){
	var ventana_ancho = jQuery(window).width();
	var ventana_alto = jQuery(window).height();
});

