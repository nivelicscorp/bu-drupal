(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.nodeDetailsSummaries = {
    attach: function attach(context) {
      $(".page-eventos #memorias-eventos .title-block").first().text(Drupal.t('MEMORIES OF OTHER EVENTS'));
      $(".page-eventos #memorias-eventos .link-red").attr('href', '/es/insights/memorias');
      $(".page-eventos #memorias-eventos .link-red").text(Drupal.t('VER MÁS') + ' ' + Drupal.t('MEMORIALS'));
      if (typeof drupalSettings.insights != 'undefined') {
        if (drupalSettings.insights.more_title == 'Podcast' || drupalSettings.insights.more_title == 'Boletines') {
          jQuery(".node-type-span").parent().html(Drupal.t('OTROS') + ' <span class="node-type-span">' + drupalSettings.insights.more_title + '</span>');
        } else {
          jQuery(".node-type-span").text(drupalSettings.insights.more_title);
        }
        jQuery(".others-news-sidebar.more-block .link-red").attr('href', drupalSettings.insights.more_link);
        var link_text = jQuery(".others-news-sidebar.more-block .link-red").text();
        if (link_text == 'VER MÁS ' || link_text == 'VIEW MORE ') {
          jQuery(".others-news-sidebar.more-block .link-red").text(link_text + ' ' + drupalSettings.insights.more_title);
        }
      }
      jQuery(".addtocalendar > a").text(Drupal.t("AÑADIR AL CALENDARIO"));
      $(".block-newsletter").attr('id', 'block-newsletter');
      setTimeout(function () {
        $(".select2-search__field").attr('placeholder', Drupal.t('Áreas de práctica'));
        $(".select2-search__field").css('width', '');
      }, 1000);

    }
  }
})(jQuery, Drupal, drupalSettings);

jQuery(document).on("click", '.message-mail .close', function () {
  jQuery(this).parent().hide();
});

jQuery(document).ready(function () {


  jQuery('.field--name-field-archivo-des').hide();
  jQuery("#webform-submission-descarga-informe-node-1812-add-form #edit-actions-submit").click(function () {
    var url = jQuery('.field--name-field-archivo-des').text();
    window.open('https://' + window.location.hostname + url);
    jQuery("input[name='archivo']").val('https://' + window.location.hostname + url);
  });

  // jQuery(".addtocalendar > a").text("AÑADIR AL CALENDARIO");
  jQuery(".addtocalendar .atcb-list:last-child").remove();
  if (jQuery('.breadcrumb li').length > 0) {
    jQuery('.breadcrumb li:first-child').addClass('h-breadcrumb1');
    jQuery('.breadcrumb li:nth-child(2)').addClass('h-breadcrumb2');
    jQuery('.breadcrumb li:nth-child(3)').addClass('h-breadcrumb3');
  }

  if (jQuery(".page-node-type-noticia .banner-internas").length > 0) {
    jQuery(".page-node-type-noticia .banner-internas").addClass('h-entry');
    jQuery(".page-node-type-noticia .banner-internas h1").addClass('p-name');
    jQuery(".page-node-type-noticia .banner-internas .category").addClass('p-category');
  }

  if (jQuery(".page-node-type-noticia .content-page-full article.noticia").length > 0) {
    jQuery(".page-node-type-noticia .content-page-full article.noticia").addClass('h-entry');
    jQuery(".page-node-type-noticia .content-page-full article.noticia .content").addClass('e-content');
    jQuery(".page-node-type-noticia .content-page-full article.noticia .content .field--name-field-image").addClass('u-photo');
  }
  if (jQuery(".lawyer-card-info").length > 0) {
    var mailLawyer = jQuery(".lawyer-card-info .field--name-field-email").text();
    var telLawyer = jQuery(".lawyer-card-info .field--name-field-telephone .field--item").text();
    jQuery(".lawyer-card-info .field--name-field-email").append("<a href='mailto:" + mailLawyer + "'>" + mailLawyer + "</a>");
    jQuery(".lawyer-card-info .field--name-field-telephone .field--item").append("<a href='tel:" + telLawyer + "'>" + telLawyer + "</a>");
  }

  var lenguajeCurrent = jQuery("html").attr("lang");
  if (lenguajeCurrent === "es" && jQuery(".page-valor-compartido")) {
    //jQuery(".buzon-sugerencias-container .content-banner-f h2").text("Buzón de Fundaciones");
  }
  /*else if(lenguajeCurrent === "es" && jQuery(".path-que-hacemos")){
  jQuery(".buzon-sugerencias-container .content-banner-f h2").text("Nuestra Experiencia");
  jQuery(".buzon-sugerencias-container .content-banner-f .btn-red").attr('href','/es/que-hacemos/experiencia');
}*/

  jQuery(".view-duplicado-de-noticias-asociadas-boletines .views-row .id-boletin-padre").each(function () {
    var idb = jQuery(this).text();
    var href = jQuery(this).parent().parent().find('.views-field-field-image a').attr('href');
    jQuery(this).parent().parent().find('.views-field-field-image a').attr('href', href + '?boletin=' + idb);
    jQuery(this).parent().parent().find('.views-field-title a').attr('href', href + '?boletin=' + idb);
    jQuery(this).parent().parent().find('.views-field-view-node a').attr('href', href + '?boletin=' + idb);
  });

  if (jQuery(".page-what-we-do")) {
    // jQuery(".page-what-we-do .attachment .view-what-we-doing .view-header p, .page-what-we-do .attachment .view-what-we-doing .view-header div").text("Industries");
  }



  getScrollBarWidth();
  setInterval(function () {
    jQuery(".modal-header .close, .views-exposed-form .form-submit").click(function () {
      jQuery("body").removeClass("general-search");
    });
  }, 1000);

  setTimeout(function () {
    jQuery("span.filename").click(function () {
      jQuery(this).parent().find("input").click();
    });
  }, 2000);

  var tid = "";
  if (jQuery(".taxonomy-term-page-areas").length > 0) {
    set_lateral_active();
    tid = jQuery(".taxonomy-term-page-areas").attr("data-tid");
    var href = jQuery(".nuestros-abogados .field--name-field-url a").attr("href");
    jQuery(".nuestros-abogados .field--name-field-url a").attr("href", href + '?field_area_target_id=' + tid);
  }
  if (jQuery(".taxonomy-term-page-indrustrias").length > 0) {
    set_lateral_active();
    tid = jQuery(".taxonomy-term-page-indrustrias").attr("data-tid");
    var href = jQuery(".nuestros-abogados .field--name-field-url a").attr("href");
    jQuery(".nuestros-abogados .field--name-field-url a").attr("href", href + '?field_industrias_target_id=' + tid);
  }
  if (jQuery(".taxonomy-term-page-nuevos-servicios").length > 0) {
    set_lateral_active();
    tid = jQuery(".taxonomy-term-page-nuevos-servicios").attr("data-tid");
    var href = jQuery(".nuestros-abogados .field--name-field-url a").attr("href");
    jQuery(".nuestros-abogados .field--name-field-url a").attr("href", href + '?field_industrias_target_id=' + tid);
  }

  jQuery(".chosen-search-input").val("Áreas de práctica");

  jQuery(window).scroll(function () {
    var scroll = jQuery(window).scrollTop();
    if (scroll >= 50) {
      jQuery(".entity-node-canonical .view-header .title-news").css("color", "#002d73");
    } else {
      jQuery(".entity-node-canonical .view-header .title-news").css("color", "white");
    }
  });

  jQuery(window).scroll(function () {
    var scroll = jQuery(window).scrollTop();
    if (scroll >= 400) {
      jQuery(".header-site").addClass("fixed");
      jQuery(".header-mobile .top-header").addClass("menu-bk");
      jQuery(".sidebar-news .others-news-sidebar .form-group:first-child .title-block , .sidebar-news .others-news-sidebar .first-title").css("color", "#002d73");
      jQuery(".page-node-type-memoria .sidebar-news .others-news-sidebar .title-block").css("color", "#002d73");
    } else {
      jQuery(".header-site").removeClass("fixed");
      jQuery(".header-mobile  .top-header").removeClass("menu-bk");
      jQuery(".sidebar-news .others-news-sidebar .form-group:first-child .title-block, .sidebar-news .others-news-sidebar .first-title").css("color", "white");
      jQuery(".page-node-type-memoria .sidebar-news .others-news-sidebar .title-block").css("color", "white");
    }
    if (jQuery('.content-banner-footer').length > 0) {
      jQuery('.section-indicadores').toggleClass('animate',
        scroll >= jQuery('.content-banner-footer').offset().top
      );
    }
  });
  jQuery(window).scroll(function () {
    var scroll2 = jQuery(window).scrollTop();
    if (scroll2 >= 10) {
      jQuery(".header-site").addClass("fixed");
      jQuery(".header-mobile .top-header").addClass("menu-bk");
    }

    jQuery(".we-mega-menu-ul  .we-mega-menu-li").mouseenter(function () {
      jQuery(".header-site").addClass("hover-menu");
    });
    jQuery(".we-mega-menu-ul").mouseleave(function () {
      jQuery(".header-site").removeClass("hover-menu");
    });
  });
  /*function setHeight() {
      windowHeight = jQuery(window).innerHeight();
      jQuery('.container-404').css('min-height', windowHeight);
  };
  setHeight();*/
  var ventana_alto = jQuery(window).height();
  var ventana_ancho = jQuery(window).width();
  var footerH = jQuery('.footer').outerHeight();
  jQuery("#timelineiframe, #timelineiframemobile").css("height", ventana_alto);

  jQuery(".form-item-adjuntar-hoja-de-vida input:file").uniform({
    fileButtonHtml: false,
    fileDefaultHtml: "Adjuntar hoja de vida"
  });

  jQuery(".form-item-adjuntar-carta-de-interes input:file").uniform({
    fileButtonHtml: false,
    fileDefaultHtml: "Adjuntar carta de interés"
  });
  jQuery('.block-newsletter .despliegue').click(function () {
    jQuery('.block-newsletter .open-newsletter').toggle("slow");
    jQuery(this).hide();
    jQuery('.block-newsletter').addClass('form-desplegado');
  });
  jQuery('.banner-internas .down-arrow').click(function () {
    jQuery('html,body').animate({
      scrollTop: jQuery(".content , .content-page-full , .region-content").offset().top
    }, 'slow');
  });

  setInterval(function () {
    var classIframe = jQuery(".page-experiencia #timelineiframe, .page-experience #timelineiframe").contents().find("body").attr("class");
    var classIframem = jQuery(".page-experiencia #timelineiframemobile , .page-experience #timelineiframemobile").contents().find("body").attr("class");
    if (classIframe === "section-active3item") {
      jQuery("body").addClass("change-header");
      jQuery("body").removeClass("change-header-zero");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-four");
    } else if (classIframe === "section-active0item") {
      jQuery("body").addClass("change-header-zero");
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-four");
    } else if (classIframe === "section-active1item") {
      jQuery("body").addClass("change-header-one");
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-zero");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-four");
    } else if (classIframe === "section-active2item") {
      jQuery("body").addClass("change-header-two");
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-zero");
      jQuery("body").removeClass("change-header-four");
    } else if (classIframe === "section-active4item") {
      jQuery("body").addClass("change-header-four");
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-zero");
    } else if (classIframe === "section-active5item") {
      jQuery("body").addClass("change-header-four");
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-zero");
    } else if (classIframem === "fp-viewing-yearThree") {
      jQuery("body").addClass("change-header");
    } else if (classIframem === "fp-viewing-yearFour") {
      jQuery("#timelineiframemobile").css("height", ventana_alto - 20);
      jQuery("body").removeClass("change-header");
    }
    else {
      jQuery("body").removeClass("change-header");
      jQuery("body").removeClass("change-header-two");
      jQuery("body").removeClass("change-header-one");
      jQuery("body").removeClass("change-header-four");
      jQuery("#timelineiframemobile").css("height", ventana_alto);
    }
  }, 1000);

  /* Width - txt btn */
  var txtW = jQuery(".btn-red .text, .btn-red a").width();

  if (ventana_ancho > 1150) {
    jQuery(".btn-red").hover(function () {
      jQuery(this).css("width", txtW + 140);
      jQuery(this).addClass("hover");
    }, function () {
      jQuery(this).css("width", 60);
      jQuery(this).removeClass("hover");
    });
  }
  //Slider
  jQuery(".taxonomy-term-page-areas .view-id-lawyers .view-content, .taxonomy-term-page-indrustrias .view-id-lawyers .view-content, .taxonomy-term-page-nuevos-servicios .view-id-lawyers .view-content").slick({
    slidesToShow: 4,
    dots: true,
    arrows: true,
    margin: 10,
    responsive: [
      {
        breakpoint: 969,
        settings: {
          slidesToShow: 1
        }
      }
    ]
  });
  if (ventana_ancho <= 767) {
    jQuery(".section-indicadores .field--name-field-items-indicators").slick({
      dots: true,
      slidesToShow: 1
    });
    jQuery('footer .title-section-f').click(function () {
      jQuery(this).toggleClass('open-links-f');
      jQuery(this).next().toggle("slow");
    });
  }

  if (ventana_ancho <= 960) {
    jQuery(".accordion-slick").slick({
      dots: true,
      slidesToShow: 1
    });


    /*funcion para ocultar/mostar menu ingles/español en mobile*/
    var lenguajeCurrent = jQuery("html").attr("lang");
    if (lenguajeCurrent === "es") {
      jQuery(".header-mobile-es").show();
      jQuery(".header-mobile-en").hide();
    }
    else if (lenguajeCurrent === "en") {
      jQuery(".header-mobile-en").show();
      jQuery(".header-mobile-es").hide();
    }
  }
  //Agregar al calendario
  jQuery(".addtocalendar .atcb-link").click(function () {
    jQuery(this).parent().find(".atcb-list").toggleClass("open");
  });

  var heightAside = jQuery('aside').height();
  var heightGeneral = jQuery(window).height() - jQuery("header").height();

  console.log('DEBUG fixedOptions:', 'aside.height=', heightAside, 'heightGeneral=', heightGeneral, 'block-newsletter=', jQuery('.block-newsletter').length, 'aside=', jQuery('aside').length, 'width=', jQuery(window).width());
  fixedOptions(heightAside, heightGeneral);

  var $allCollapsibles = jQuery('.collapsibe-block-title');
  jQuery('.collapsibe-block-title').click(function () {
    heightAside = jQuery('aside').height();
    $allCollapsibles.not(this).parent().find(".collapsibe-block-items").slideUp();
    $allCollapsibles.not(this).addClass('close-item');
    jQuery(this).parent().find(".collapsibe-block-items").slideToggle();
    jQuery(this).toggleClass('close-item');
  });

  /*menu mobile*/
  jQuery('.toggle-menu').click(function () {
    jQuery(this).toggleClass('toggle-close-m');
    jQuery('.main-container').toggleClass("menu-abierto");
    jQuery('.header-mobile .content-menu-toggle').toggleClass("toggle-open-m");
    jQuery('.header-mobile .top-header , body').toggleClass("overlay");
    jQuery(this).parent().toggleClass("toggle-open-m");
  });

  jQuery('.menu-mob .item-menu .arrow').click(function () {
    if (jQuery('.menu-mob .item-menu').hasClass('item-desplegable')) {
      jQuery('.menu-mob .item-menu .link-principal').removeClass("active");
      jQuery(this).find('.link-principal').addClass("active");
      jQuery(this).toggleClass('open-sub-menu');
      jQuery(this).next().toggle("slow");
    }
  });
  //var ventana_ancho = jQuery(window).width();
  jQuery(".wrap-main-banner .item").slick({
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

  if (ventana_ancho > 1150) {
    jQuery(".btn-red").hover(function () {
      jQuery(this).css("width", txtW + 130);
      jQuery(this).addClass("hover");
    }, function () {
      jQuery(this).css("width", 60);
      jQuery(this).removeClass("hover");
    });
    var heightSidebar = jQuery(".sidebar-news").height();
    jQuery(".content-page-full").css("min-height", heightSidebar);
  }

  jQuery(".message-mail .close").click(function () {
    jQuery(this).parent().hide();
  });

});

function fixedOptions(heightAside, heightGeneral) {
  var altura = heightAside;
  var ventana_ancho = jQuery(window).width();
  var href = location.href;
  var pathHref = href.substring(href.lastIndexOf('/') + 1);

  if (ventana_ancho > 960 && jQuery('.block-newsletter').length && jQuery('aside').length) {
    if (href.indexOf("que-hacemos") > -1) {
      //console.log('que hacemos');
      jQuery("aside").css("height", heightGeneral);
      jQuery('aside').scrollToFixed({
        marginTop: 150,
        removeOffsets: false,
        limit: jQuery('.block-newsletter').offset().top - heightGeneral - 150,
        unfixed: function () {
          jQuery(this).addClass('absolute');
        },
        fixed: function () {
          jQuery(this).removeClass('absolute');
        }
      });
    }
    else if (pathHref === 'insights') {
      //console.log('insights');
      jQuery('aside').scrollToFixed({
        marginTop: 150,
        removeOffsets: true,
        limit: jQuery('.block-newsletter').offset().top - jQuery('aside').height() - 150
      });
    }
    else {
      //console.log('default');
      jQuery('aside').scrollToFixed({
        marginTop: 150,
        removeOffsets: false,
        limit: function () {
          return jQuery('.block-newsletter').offset().top - jQuery('aside').height();
        },
        unfixed: function () {
          jQuery(this).addClass('absolute');
        },
        fixed: function () {
          jQuery(this).removeClass('absolute');
        }
      });

    }
  }
}

function getScrollBarWidth() {
  var $outer = jQuery('<div>').css({ visibility: 'hidden', width: 100 + '%', overflow: 'scroll' }).appendTo('body'),
    widthWithScroll = jQuery('<div>').css({ width: '100%' }).appendTo($outer).outerWidth();
  $outer.remove();
  var scrollWidth = 100 - widthWithScroll;
  var classIframe = jQuery(".page-experiencia .header-site, .page-experience .header-site").css({
    width: 100 - scrollWidth
  });
};
jQuery(window).resize(function () {
  getScrollBarWidth();
  var ventana_ancho = jQuery(window).width();
  var ventana_alto = jQuery(window).height();
  /*setHeight();*/
});


(function ($, Drupal, drupalSettings) {
  'use strict';
  $(".path-noticias .select-items div").click(function (e) {
    var value = $(this).attr('value');
    $(".views-auto-submit-click").click();
    //window.location  = '/taxonomy/term/' + value;
  });
  $(".path-eventos .select-items div").click(function (e) {
    var value = $(this).attr('value');
    $(".views-auto-submit-click").click();
    //window.location  = '/taxonomy/term/' + value;
  });
  $(".path-que-hacemos .select-items div").click(function (e) {
    var value = $(this).attr('value');
    var text = $(this).text();
    console.log(text);
    if (text != "Sectores e industrias" && text != "Nuevos servicios" && text != "New services" && text != "Área de práctica" && text != "Practice area" && text != "Industry") {
      var href = $("a:contains('" + text + "')").attr('href');
      window.location.href = href;
    }
    //window.location  = '/taxonomy/term/' + value;
  });
})(jQuery, Drupal, drupalSettings);

jQuery(document).ajaxComplete(function () {
  /*formulario estilos cargar archivo*/
  jQuery(".form-item-adjuntar-hoja-de-vida input:file").uniform({
    fileButtonHtml: false,
    fileDefaultHtml: ""
  });

  jQuery(".form-item-adjuntar-carta-de-interes input:file").uniform({
    fileButtonHtml: false,
    fileDefaultHtml: ""
  });

  if (jQuery(".select-selected").length == 0) {
    //if (jQuery(".page-noticias").length > 0) {
    if (jQuery("#views-exposed-form-lawyers-search-page").length > 0) {
      console.log("in - 1")
      if (drupalSettings.path.currentLanguage == 'en') {
        console.log("in - 2")
        jQuery('[name="field_ciudad_target_id"] option:contains("Londres")').text('London');
        jQuery('[name="field_ciudad_target_id"] option:contains("Singapur")').text('Singapore');

      }
    }
    var x, i, j, selElmnt, a, b, c, value_select;
    x = document.getElementsByClassName("select-wrapper");
    for (i = 0; i < x.length; i++) {
      selElmnt = x[i].getElementsByTagName("select")[0];

      a = document.createElement("DIV");
      a.setAttribute("class", "select-selected");
      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
      x[i].appendChild(a);

      b = document.createElement("DIV");
      b.setAttribute("class", "select-items select-hide");
      for (j = 0; j < selElmnt.length; j++) {
        c = document.createElement("DIV");

        var valurOpt = selElmnt.options[j].value;

        if (drupalSettings.path.currentLanguage === "en" && valurOpt.toString() === "1420") {
          c.innerHTML = "London";
          console.log(selElmnt.options[j].innerHTML);
        }
        else if (drupalSettings.path.currentLanguage === "en" && valurOpt.toString() === "1421") {
          c.innerHTML = "Singapore";
          console.log(selElmnt.options[j].innerHTML);
        }
        else {
          c.innerHTML = selElmnt.options[j].innerHTML;
        }
        value_select = selElmnt.options[j].value;
        c.setAttribute('value', value_select);
        c.addEventListener("click", function (e) {
          var y, i, k, s, h;

          s = this.parentNode.parentNode.getElementsByTagName("select")[0];
          h = this.parentNode.previousSibling;

          for (i = 0; i < s.length; i++) {
            if (s.options[i].innerHTML == this.innerHTML) {
              s.selectedIndex = i;
              h.innerHTML = this.innerHTML;
              console.log("select 1:", this.innerHTML);
              y = this.parentNode.getElementsByClassName("same-as-selected");
              for (k = 0; k < y.length; k++) {
                y[k].removeAttribute("class");
              }
              this.setAttribute("class", "same-as-selected");
              break;
            }
          }
          h.click();
        });
        b.appendChild(c);
      }
      x[i].appendChild(b);
      a.addEventListener("click", function (e) {
        e.stopPropagation();
        closeAllSelect(this);
        this.nextSibling.classList.toggle("select-hide");
        this.classList.toggle("select-arrow-active");
      });
    }
    jQuery(".path-noticias .select-items div").click(function (e) {
      var value = jQuery(this).attr('value');
      jQuery(".views-auto-submit-click").click();
    });
    jQuery(".path-eventos .select-items div").click(function (e) {
      var value = jQuery(this).attr('value');
      jQuery(".views-auto-submit-click").click();
      //window.location  = '/taxonomy/term/' + value;
    });
    //}
  }

  var total = jQuery(".webform-multiple-table--operations .btn-success").length;
  if (total > 1) {
    var c = 1;
    jQuery(".webform-multiple-table--operations .btn-success").each(function () {
      if (c < total) {
        jQuery(this).hide();
        c++;
      }
    });
  }

});
if (document.getElementById('webform_submission_boletin_node_1_add_form-ajax') != null) {
  document.getElementById('webform_submission_boletin_node_1_add_form-ajax').addEventListener('submit', function (evt) {
    evt.preventDefault();
    return submitForm();
  });
}

function set_lateral_active() {
  var url = window.location.pathname;
  url = url.split("/");
  var count = url.length;
  var final = url[count - 1];
  if (jQuery(".block-views-blockwhat-we-doing-collapsibe-block-1").length > 0 || jQuery(".block-views-blockwhat-we-doing-collapsibe-block-2").length > 0 || jQuery(".block-views-blockwhat-we-doing-collapsibe-block-3").length > 0) {
    var activeLink = jQuery("a[href$='" + final + "']").addClass('active');
    if (activeLink.length > 0) {
      var parentBlock = activeLink
        .closest(".collapsibe-block-items")
        .css("display", "block");
      jQuery(".collapsibe-block-items")
        .not(parentBlock)
        .css("display", "none");
    }
  }
}

function submitForm() {
  //saca los atributos del form
  var atributos = {
    "nombre": document.getElementById("edit-nombre-completo").value,
    "empresa": document.getElementById("edit-empresa").value,
    "email": document.getElementById("edit-correo-electronico-").value,
  };
  //genera el evento
  var evento = {
    "eventName": "registro_site2",
    "email": document.getElementById("edit-correo-electronico-").value,
    "attributes": atributos
  };


  return jQuery.ajax({
    url: "https://track.embluemail.com/contacts/event",
    headers: {
      "Authorization": "Basic Nzk4MzBiZDllN2I0NDE3NDk0YjNhZGE3MmRlM2I3OTY="
    },
    data: JSON.stringify(evento),
    contentType: "application/json",
    dataType: "json",
    type: "POST"
  }).
    done(
      () => true
      //lo que quieran que pase cuando funciona!
    ).
    fail(
      //lo que quieran que pase cuando no funciona!
      () => false
    )
}

function ancla(item) {
  jQuery('body,html').stop(true, true).animate({
    scrollTop: jQuery('.' + item).offset().top - 150
  }, 1000);

};

