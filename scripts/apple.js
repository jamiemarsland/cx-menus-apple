(function ($) {

    $(document).ready(function () {
        var menuItemCount = $('#navigation_apple #main-nav > li').length;
        var percentage = 100 / menuItemCount;
        $('#navigation_apple #main-nav > li').css('width', percentage + '%');
    });

})(jQuery);

