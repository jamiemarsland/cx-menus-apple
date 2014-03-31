(function ($) {

    $(document).ready(function () {
        var $appleMenuSearchSection = null;
        $('#woo-option-applemenu .section').each(function () {
           if ($(this).find('.heading').text() == 'Enable Search') {
               $appleMenuSearchSection = $(this);
           }
        });

        var $primaryNavSearchSection = null;
        $('#woo-option-primarynavigation .section').each(function () {
            if ($(this).find('.heading').text() == 'Enable Search') {
                $primaryNavSearchSection = $(this);
            }
        });

        $appleMenuSearchSection.find('input[type=checkbox]').click(function () {
            if ($(this).is(':checked')) {
                $primaryNavSearchSection.find('input[type=checkbox]').prop('checked', true);
            } else {
                $primaryNavSearchSection.find('input[type=checkbox]').prop('checked', false);
            }
        });

        $primaryNavSearchSection.find('input[type=checkbox]').click(function () {
            if ($(this).is(':checked')) {
                $appleMenuSearchSection.find('input[type=checkbox]').prop('checked', true);
            } else {
                $appleMenuSearchSection.find('input[type=checkbox]').prop('checked', false);
            }
        });

        var $appleMenuMarginSection = null;
        $('#woo-option-applemenu .section').each(function () {
            if ($(this).find('.heading').text() == 'Navigation Margin Top/Bottom') {
                $appleMenuMarginSection = $(this);
            }
        });

        var $primaryNavMarginSection = null;
        $('#woo-option-primarynavigation .section').each(function () {
            if ($(this).find('.heading').text() == 'Navigation Margin Top/Bottom') {
                $primaryNavMarginSection = $(this);
            }
        });

        $appleMenuMarginSection.find('input[name=woo_nav_margin_top]').change(function () {
            $primaryNavMarginSection.find('input[name=woo_nav_margin_top]').val($(this).val());
        });
        $appleMenuMarginSection.find('input[name=woo_nav_margin_bottom]').change(function () {
            $primaryNavMarginSection.find('input[name=woo_nav_margin_bottom]').val($(this).val());
        });

        $primaryNavMarginSection.find('input[name=woo_nav_margin_top]').change(function () {
            $appleMenuMarginSection.find('input[name=woo_nav_margin_top]').val($(this).val());
        });
        $primaryNavMarginSection.find('input[name=woo_nav_margin_bottom]').change(function () {
            $appleMenuMarginSection.find('input[name=woo_nav_margin_bottom]').val($(this).val());
        });
    });

})(jQuery);
