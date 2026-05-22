/**
<<<<<<< HEAD
 * cbpFixedScrollLayout.js v1.0.0
=======
 * cbpFixedScrollLayout.min.js v1.0.0
>>>>>>> 1d858043270df32f80587dce4bc9a925000b27ab
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 */
var cbpFixedScrollLayout = (function() {

	// cache and initialize some values
	var config = {
		// the cbp-fbscroller's sections
		$sections : $( '#cbp-fbscroller > section' ),
		// the navigation links
		$navlinks : $( '#cbp-fbscroller > nav:first > a' ),

		$navMenu : $( '#cbp-fbscroller > .menu:first > a' ),
		// index of current link / section
		currentLink : 0,
		// the body element
		$body : $( 'html, body' ),
		// the body animation speed
		animspeed : 650,
		// the body animation easing (jquery easing)
		animeasing : 'easeInOutExpo'
	};

	function init() {
		// click on a navigation link: the body is scrolled to the position of the respective section
		config.$navlinks.on( 'click', function() {
			scrollAnim( config.$sections.eq( $( this ).index() ).offset().top );
			return false;
		} );

		// click on a navigation link: the body is scrolled to the position of the respective section
		config.$navMenu.on( 'click', function() {
			scrollAnim( config.$sections.eq( $( this ).index() ).offset().top );
			return false;
		} );

		// 2 waypoints defined:
		// First one when we scroll down: the current navigation link gets updated. A "new section" is reached when it occupies more than 70% of the viewport
		// Second one when we scroll up: the current navigation link gets updated. A "new section" is reached when it occupies more than 70% of the viewport
		config.$sections.waypoint( function( direction ) {
			if( direction === 'down' ) {

				$('body').removeClass(function() {
				  return $( this ).attr( "class" );
				});
				changeNav( $( this ) );

				var ActivecurrentLink = config.currentLink;	
				var currentClass = ActivecurrentLink + 'item';
				$('body').addClass('section-active'+ currentClass);

			}
		}, { offset: '30%' } ).waypoint( function( direction ) {
			if( direction === 'up' ) { 
				$('body').removeClass(function() {
				  return $( this ).attr( "class" );
				});

				changeNav( $( this ) );

				var ActivecurrentLink = config.currentLink;	
				var currentClass = ActivecurrentLink + 'item';
				$('body').addClass('section-active'+ currentClass);

			}
		}, { offset: '-30%' } );

		// on window resize: the body is scrolled to the position of the current section
		$( window ).on( 'debouncedresize', function() {
			scrollAnim( config.$sections.eq( config.currentLink ).offset().top );
		} );
		
	}

	// update the current navigation link
	function changeNav( $section ) {
		config.$navlinks.eq( config.currentLink ).removeClass( 'cbp-fbcurrent' );
		config.$navMenu.eq( config.currentLink ).removeClass( 'cbp-fbcurrent' );
		config.currentLink = $section.index( 'section' );
		config.$navlinks.eq( config.currentLink ).addClass( 'cbp-fbcurrent' );
		config.$navMenu.eq( config.currentLink ).addClass( 'cbp-fbcurrent' );
	}

	// function to scroll / animate the body
	function scrollAnim( top ) {
		config.$body.stop().animate( { scrollTop : top }, config.animspeed, config.animeasing );
	}

	return { init : init };

    var a = {
        $sections: $(".cbp-fbscroller > section"),
        $navlinks: $(".cbp-fbscroller > .menuYears > a"),
        currentLink: 0,
        $body: $("html, body"),
        animspeed: 650,
        animeasing: "easeInOutExpo"
    };

    function d() {
        a.$navlinks.on("click", function() {
            c(a.$sections.eq($(this).index()).offset().top);
            return false
        });
        a.$sections.waypoint(function(e) {
            if (e === "down") {
                b($(this))
            }
        }, {
            offset: "30%"
        }).waypoint(function(e) {
            if (e === "up") {
                b($(this))
            }
        }, {
            offset: "-30%"
        });
        $(window).on("debouncedresize", function() {
            c(a.$sections.eq(a.currentLink).offset().top)
        })
    }

    function b(e) {
        a.$sections.eq(a.currentLink).removeClass("active");
        a.$navlinks.eq(a.currentLink).removeClass("cbp-fbcurrent");
        a.currentLink = e.index("section");
        a.$navlinks.eq(a.currentLink).addClass("cbp-fbcurrent");
        a.$sections.eq(a.currentLink).addClass("active");
    }

    function c(e) {
        a.$body.stop().animate({
            scrollTop: e
        }, a.animspeed, a.animeasing)
    }
    return {
        init: d
    }
})();