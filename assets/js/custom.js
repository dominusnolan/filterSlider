jQuery (document ).ready(function($) {
    $('.flexslider.top-companies').flexslider({
        animation: "slide",
        animationLoop: false,
        itemWidth: 140,
        itemMargin: 10,
        start: function(){
            $(window).trigger('resize');
        }
    });

});

