jQuery(document).ready(function() {
    var ventana_ancho = jQuery(window).width();
    jQuery(".view-view-main-banner .wrap-main-banner").slick({
		dots: true,
		slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 10000,
		responsive: [
		  {
			breakpoint: 767,
			settings: {
			  arrows: true,
			  dots: true
			}
		  }
		]
    });
    var txtW = jQuery(".btn-red .text, .btn-red a").width();

	if(ventana_ancho > 1150){
		jQuery(".btn-red").hover(function(){
			jQuery(this).css("width", txtW + 140);
			jQuery(this).addClass("hover");
		}, function(){
			jQuery(this).css("width", 60);
			jQuery(this).removeClass("hover");
		});
	}
});