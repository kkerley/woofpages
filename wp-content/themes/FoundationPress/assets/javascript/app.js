jQuery(document).ready(function($){
    var $root = $('html, body');

    // nav transformation on scroll
    $(window).scroll(function() {
        var winTop = $(window).scrollTop();
        if (winTop >= 30) {
            $("body").addClass("sticky-shrinknav-wrapper");
        } else{
            $("body").removeClass("sticky-shrinknav-wrapper");
        }
    });
    // end nav transformation on scroll

    $('.featured-dogs--inner').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        infinite: true,

    });

    $('.carousel--dog-detail-page_primary').slick({
        arrows: true,
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
    });

    $('.jump-link').on('click', function(e){
        e.preventDefault();
        var jumpTarget = $(this).data('jump-to');

        if($(this).hasClass('single-dog-page-application')){
            $root.animate({
                scrollTop: $('#' + jumpTarget ).offset().top - 270
            }, 500);
        } else{
            $root.animate({
                scrollTop: $('#' + jumpTarget ).offset().top - 41
            }, 500);
        }
    });

    $('.kk-modal-trigger').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var modalToOpen = '.' + $this.data('target-modal');

        $(modalToOpen).toggleClass('is-showing');
    });

    // Mix It Up
    if($('.post-type-archive-dog .wrapper--dogs').length){
        var dog_mixer = mixitup('.post-type-archive-dog .wrapper--dogs', {
            controls: {
                toggleLogic: 'and'
            },
            animation: {
                effects: 'fade translateZ(-100px)',
            }
        });
    }
    // End of Mix It Up

});