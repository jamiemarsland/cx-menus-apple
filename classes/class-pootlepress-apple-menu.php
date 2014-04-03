<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Pootlepress_Apple_Menu Class
 *
 * Base class for the Pootlepress Apple Menu.
 *
 * @package WordPress
 * @subpackage Pootlepress_Apple_Menu
 * @category Core
 * @author Pootlepress
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * public $token
 * public $version
 * private $_menu_style
 * 
 * - __construct()
 * - add_theme_options()
 * - get_menu_styles()
 * - load_style_specific_method()
 * - load_style_specific_stylesheet()
 * - load_localisation()
 * - check_plugin()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - get()
 * - style_hooks_top_tabs()
 * - style_hooks_header()
 * - style_hooks_beautiful_type()
 * - style_hooks_top_align()
 * - style_hooks_centred()
 * - move_nav_inside_header()
 */
class Pootlepress_Apple_Menu {
	public $token = 'pootlepress-apple-menu';
	public $version;
	private $file;
	private $_menu_style;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->file = $file;
		$this->load_plugin_textdomain();
		add_action( 'init', 'check_main_heading', 0 );
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $file, array( &$this, 'activation' ) );

		// Add the custom theme options.
		add_filter( 'option_woo_template', array( &$this, 'add_theme_options' ) );

		// Lood for a method/function for the selected style and load it.
		add_action( 'get_header', array( &$this, 'load_style_specific_method' ) , 1000);

		// Lood for a stylesheet for the selected style and load it.
		add_action( 'wp_enqueue_scripts', array( &$this, 'load_style_specific_stylesheet' ) );
        add_action( 'wp_enqueue_scripts', array( &$this, 'load_script' ) );

        add_action('admin_print_scripts', array(&$this, 'load_admin_script'));

        add_action('wp_head', array(&$this, 'option_css'));
	} // End __construct()

    public function load_script() {
        $pluginFile = dirname(dirname(__FILE__)) . '/pootlepress-apple-menu.php';
        wp_enqueue_script('pootlepress-apple', plugin_dir_url($pluginFile) . 'scripts/apple.js', array('jquery'));
    }

    public function load_admin_script() {
        $screen = get_current_screen();
        if ($screen->base == 'toplevel_page_woothemes') {
            $pluginFile = dirname(dirname(__FILE__)) . '/pootlepress-apple-menu.php';
            wp_enqueue_script('pootlepress-apple-admin', plugin_dir_url($pluginFile) . 'scripts/apple-admin.js', array('jquery'));
        }

    }

	/**
	 * Add theme options to the WooFramework.
	 * @access public
	 * @since  1.0.0
	 * @param array $o The array of options, as stored in the database.
	 */
	public function add_theme_options ( $o ) {
		//If the Canvas Extensions is not installed
		$styles = array();
		
		foreach ( (array)$GLOBALS['pootlepress_apple_menu']->get_menu_styles() as $k => $v ) {
			if ( isset( $v['name'] ) ) {
				$styles[$k] = $v['name'];
			}
		}
		
		$o[] = array(
				'name' => __( 'Apple Menu', 'pootlepress-apple-menu' ),
				'type' => 'subheading'
				);
        $o[] = array(
            'name' => 'Papple Menu',
            'desc' => '',
            'id' => 'pootlepress-apple-menu-notice',
            'std' => 'For help and support please email support@pootlepress.com. For more great Canvas menus visit the <a href="http://www.pootlepress.com/store">PootlePress Store</a>',
            'type' => 'info'
        );
        $o[] = array(
            'id' => 'pootlepress-apple-menu-enable',
            'name' => __( 'Use Apple Menu', 'pootlepress-apple-menu' ),
            'desc' => __( 'Enable Apple Menu', 'pootlepress-apple-menu' ),
            'std' => 'true',
            'type' => 'checkbox'
        );
        $o[] =	array(
            'id' => 'pootlepress-apple-menu-base-color',
            'name' => 'Base Colour',
            'desc' => 'Pick a base colour for Apple menu',
            'std' => '#000000',
            'type' => 'color'
        );
        $o[] = array(
            'id' => 'pootlepress-apple-menu-remove-logo',
            'name' => __( 'Remove Site Title/Logo', 'pootlepress-apple-menu' ),
            'desc' => __( 'Remove Site Title/Logo', 'pootlepress-apple-menu' ),
            'std' => 'false',
            'type' => 'checkbox'
        );

        $shortname = 'woo';
        $o[] = array( "name" => __( 'Enable Search', 'woothemes' ),
            "desc" => __( 'Enable Search in the right navigation.', 'woothemes' ),
            "id" => $shortname."_nav_search",
            "std" => "false",
            "type" => "checkbox");

        $o[] = array( "name" => __( 'Navigation Margin Top/Bottom', 'woothemes' ),
            "desc" => __( 'Enter an integer value i.e. 20 for the desired header margin.', 'woothemes' ),
            "id" => $shortname."_nav_margin_tb",
            "std" => "",
            "type" => array(
                array(  'id' => $shortname. '_nav_margin_top',
                    'type' => 'text',
                    'std' => '',
                    'meta' => __( 'Top', 'woothemes' ) ),
                array(  'id' => $shortname. '_nav_margin_bottom',
                    'type' => 'text',
                    'std' => '',
                    'meta' => __( 'Bottom', 'woothemes' ) )
            ));
        return $o;
	} // End add_theme_options()


	/**
	 * Get the supported menu types available.
	 * @access public
	 * @since  1.0.0
	 * @return array Supported menu styles.
	 */
	public function get_menu_styles () {
		$styles = array(
						'none' => array(
									'name' => __( 'None', 'pootlepress-apple-menu' ),
									'callback' => 'method', 
									'stylesheet' => 'core'
									), 
						'apple' => array(
									'name' => __( 'Apple.com', 'pootlepress-apple-menu' ),
									'callback' => 'method', 
									'stylesheet' => 'core'
									),
					);
		return $styles;
	} // End get_menu_styles()

    public function option_css() {

        $enable = get_option('pootlepress-apple-menu-enable', 'true');
        if ($enable == '') {
            $enable = 'true';
        }

        if ($enable == 'true') {
            $css = '';

            $searchEnabled = get_option('woo_nav_search', 'false');
            if ($searchEnabled == 'true') {
                $css .= <<<MAINNAV
#navigation_apple #main-nav
{
    width: 95%;
}
MAINNAV;
            } else {
                $css .= <<<MAINNAV
#navigation_apple #main-nav
{
    width: 100%;
}
MAINNAV;
            }

            $marginTop = get_option('woo_nav_margin_top', '0');
            if ($marginTop == '') {
                $marginTop = '0';
            }
            $marginBottom = get_option('woo_nav_margin_bottom', '20');
            if ($marginBottom == '') {
                $marginBottom = '20';
            }
            $marginTop .= 'px';
            $marginBottom .= 'px';

            $css .= <<<MAINNAVMARGIN
#navigation_apple
{
    margin-top: $marginTop !important;
    margin-bottom: $marginBottom !important;
}
MAINNAVMARGIN;


            $baseColor = get_option('pootlepress-apple-menu-base-color', '#000000');
            if (empty($baseColor)) {
                $baseColor = '#000000';
            }

            $rHex = substr($baseColor, 1, 2);
            $gHex = substr($baseColor, 3, 2);
            $bHex = substr($baseColor, 5, 2);

            $rDec = hexdec($rHex);
            $gDec = hexdec($gHex);
            $bDec = hexdec($bHex);

            $hsv = $this->RGBtoHSV($rDec, $gDec, $bDec);

            $hue = (int)$hsv[0];
            $saturation = (int)$hsv[1];

            $navAppleColor0 = "hsl($hue, $saturation%, 43%)";
            $navAppleColor1 = "hsl($hue, $saturation%, 40%)";
            $navAppleColor2 = "hsl($hue, $saturation%, 36%)";
            $navAppleColor3 = "hsl($hue, $saturation%, 43%)";
            $navAppleColor4 = "hsl($hue, $saturation%, 50%)";

            $currentMenuItemAColor1 = "hsla($hue, $saturation%, 11%, 1)";
            $currentMenuItemAColor2 = "hsla($hue, $saturation%, 29%, 1)";

//        $currentMenuItemAFocusColor = "hsl($hue, $saturation%, 14%)";

            $css .= <<<CSSSTYLE
#navigation_apple
{
            background:$navAppleColor0;
            background:
            -o-linear-gradient(top, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 5%, rgba(0, 0, 0, 0) 97%, rgba(0, 0, 0, .45) 100%),
        -o-linear-gradient(left, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) .2%, rgba(0, 0, 0, 0) 99.8%, rgba(0, 0, 0, .2) 100%),
        url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA9QAAAAkCAMAAABfcIIyAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHVQTFRFAAAA%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F5YtmQAAAACd0Uk5TAAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmZiD6WAAAAdlJREFUeF7t3btuFEEQheFzunotGYkIERA4spn3fyeHiAgEEtsXB82s2eEJavR%2F0Wjyo75XWfHpw4MkSXNqCkA2lr2%2B%2Fvz63n15qpI0x5yTSAM52XaxJLVXf%2FkoaY6xQk2sgXy8Ql2KJf3wS5HG6HMMQg3kZNkuxVGKNLxJo48%2BxhxTpBrIx7JcXEqUKJI3zd77CjWLaiAle4U6Iixv6q33MfocTL%2BBlCy7OEqJqCFvs%2FV2m3%2BTaiAd6zb7rlHtrbc1VA%2Bm30BSa%2B%2B7RNSo4a21thbVTL%2BBnCy7rCV1rdXbtbXW%2B%2Bh%2Ft78BZLM2v6NE1Fov%2FrpC3Zl%2BA2l5H6lrrRe%2FXNu%2BqCbUQE57qGvsoW5t9D4H028gJculOKLUWuvFz%2B16O9NipAZSsm8nWpfq53ZlTQ3k9s%2BamlADZ0CogZMh1MDJEGrgZAg1cDL%2Fh5ojLSC1%2ByMtLp8A2R0un3BNFEjv%2FpooDzqA9O4fdPD0Esju8PSSIglAdsciCZQzArI7lDOat30yCg8CGe2FB1eovZcI7oMSwUBSq0TwWlQHxfyBEzgW86ftDpDase0ODfKA7A4N8mhlC2R318r2px%2BeQqLpPJDae9P5%2FmrVz491%2FWekBlKy7PXVfn9rb9xXsMD2wdOcAAAAAElFTkSuQmCC') no-repeat 50% 50%,
        -o-linear-gradient(bottom, $navAppleColor1 0, $navAppleColor2 50%, $navAppleColor3 51%, $navAppleColor4 100%)
;
    background:
        -moz-linear-gradient(top, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 5%, rgba(0, 0, 0, 0) 97%, rgba(0, 0, 0, .45) 100%),
        -moz-linear-gradient(left, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) .2%, rgba(0, 0, 0, 0) 99.8%, rgba(0, 0, 0, .2) 100%),
        url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA9QAAAAkCAMAAABfcIIyAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHVQTFRFAAAA%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F5YtmQAAAACd0Uk5TAAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmZiD6WAAAAdlJREFUeF7t3btuFEEQheFzunotGYkIERA4spn3fyeHiAgEEtsXB82s2eEJavR%2F0Wjyo75XWfHpw4MkSXNqCkA2lr2%2B%2Fvz63n15qpI0x5yTSAM52XaxJLVXf%2FkoaY6xQk2sgXy8Ql2KJf3wS5HG6HMMQg3kZNkuxVGKNLxJo48%2BxhxTpBrIx7JcXEqUKJI3zd77CjWLaiAle4U6Iixv6q33MfocTL%2BBlCy7OEqJqCFvs%2FV2m3%2BTaiAd6zb7rlHtrbc1VA%2Bm30BSa%2B%2B7RNSo4a21thbVTL%2BBnCy7rCV1rdXbtbXW%2B%2Bh%2Ft78BZLM2v6NE1Fov%2FrpC3Zl%2BA2l5H6lrrRe%2FXNu%2BqCbUQE57qGvsoW5t9D4H028gJculOKLUWuvFz%2B16O9NipAZSsm8nWpfq53ZlTQ3k9s%2BamlADZ0CogZMh1MDJEGrgZAg1cDL%2Fh5ojLSC1%2ByMtLp8A2R0un3BNFEjv%2FpooDzqA9O4fdPD0Esju8PSSIglAdsciCZQzArI7lDOat30yCg8CGe2FB1eovZcI7oMSwUBSq0TwWlQHxfyBEzgW86ftDpDase0ODfKA7A4N8mhlC2R318r2px%2BeQqLpPJDae9P5%2FmrVz491%2FWekBlKy7PXVfn9rb9xXsMD2wdOcAAAAAElFTkSuQmCC') no-repeat 50% 50%,
        -moz-linear-gradient(bottom,$navAppleColor1 0, $navAppleColor2 50%, $navAppleColor3 51%, $navAppleColor4 100%)
;
    background:
        -webkit-gradient(linear, 0 0, 0 100%, from(rgba(0, 0, 0, .2)), color-stop(0.05, rgba(0, 0, 0, 0)), color-stop(0.97, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, .45))),
        -webkit-gradient(linear, 0 0, 100% 0, from(rgba(0, 0, 0, .2)), color-stop(0.002, rgba(0, 0, 0, 0)), color-stop(0.998, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, .2))),
        url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA9QAAAAkCAMAAABfcIIyAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHVQTFRFAAAA%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F5YtmQAAAACd0Uk5TAAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8gISIjJCUmZiD6WAAAAdlJREFUeF7t3btuFEEQheFzunotGYkIERA4spn3fyeHiAgEEtsXB82s2eEJavR%2F0Wjyo75XWfHpw4MkSXNqCkA2lr2%2B%2Fvz63n15qpI0x5yTSAM52XaxJLVXf%2FkoaY6xQk2sgXy8Ql2KJf3wS5HG6HMMQg3kZNkuxVGKNLxJo48%2BxhxTpBrIx7JcXEqUKJI3zd77CjWLaiAle4U6Iixv6q33MfocTL%2BBlCy7OEqJqCFvs%2FV2m3%2BTaiAd6zb7rlHtrbc1VA%2Bm30BSa%2B%2B7RNSo4a21thbVTL%2BBnCy7rCV1rdXbtbXW%2B%2Bh%2Ft78BZLM2v6NE1Fov%2FrpC3Zl%2BA2l5H6lrrRe%2FXNu%2BqCbUQE57qGvsoW5t9D4H028gJculOKLUWuvFz%2B16O9NipAZSsm8nWpfq53ZlTQ3k9s%2BamlADZ0CogZMh1MDJEGrgZAg1cDL%2Fh5ojLSC1%2ByMtLp8A2R0un3BNFEjv%2FpooDzqA9O4fdPD0Esju8PSSIglAdsciCZQzArI7lDOat30yCg8CGe2FB1eovZcI7oMSwUBSq0TwWlQHxfyBEzgW86ftDpDase0ODfKA7A4N8mhlC2R318r2px%2BeQqLpPJDae9P5%2FmrVz491%2FWekBlKy7PXVfn9rb9xXsMD2wdOcAAAAAElFTkSuQmCC') no-repeat 50% 50%,
        -webkit-gradient(linear, 0 100%, 0 0, from($navAppleColor1), color-stop(0.5, $navAppleColor2), color-stop(0.51, $navAppleColor3), to($navAppleColor4))
;
    background-size: 100%;
}
CSSSTYLE;

            $css .= <<<CSSSTYLE2
#navigation_apple #main-nav li.current-menu-item a
{
    background:
        -o-linear-gradient(top, rgba(0, 0, 0, .8) 0, rgba(0, 0, 0, 0) 10%, rgba(0, 0, 0, 0) 100%),
        -o-linear-gradient(top, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 30%, rgba(0, 0, 0, 0) 70%, rgba(0, 0, 0, .2) 100%),
        -o-linear-gradient(left, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 20%, rgba(0, 0, 0, 0) 80%, rgba(0, 0, 0, .2) 100%),
        -o-linear-gradient(top, $currentMenuItemAColor1 0, $currentMenuItemAColor2 97%, rgba(0, 0, 0, 0) 97%, rgba(0, 0, 0, .45) 100%)
;
    background:
        -moz-linear-gradient(top, rgba(0, 0, 0, .8) 0, rgba(0, 0, 0, 0) 10%, rgba(0, 0, 0, 0) 100%),
        -moz-linear-gradient(top, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 30%, rgba(0, 0, 0, 0) 70%, rgba(0, 0, 0, .2) 100%),
        -moz-linear-gradient(left, rgba(0, 0, 0, .2) 0, rgba(0, 0, 0, 0) 20%, rgba(0, 0, 0, 0) 80%, rgba(0, 0, 0, .2) 100%),
        -moz-linear-gradient(top, $currentMenuItemAColor1 0, $currentMenuItemAColor2 97%, rgba(0, 0, 0, 0) 97%, rgba(0, 0, 0, .45) 100%)
;
    background:
        -webkit-gradient(linear, 0 0, 0 100%, from(rgba(0, 0, 0, .8)), color-stop(.1, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, 0))),
        -webkit-gradient(linear, 0 0, 0 100%, from(rgba(0, 0, 0, .2)), color-stop(.3, rgba(0, 0, 0, 0)), color-stop(.7, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, .2))),
        -webkit-gradient(linear, 0 0, 100% 0, from(rgba(0, 0, 0, .2)), color-stop(.2, rgba(0, 0, 0, 0)), color-stop(.8, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, .2))),
        -webkit-gradient(linear, 0 0, 0 100%, from($currentMenuItemAColor1), color-stop(0.97, $currentMenuItemAColor2), color-stop(0.97, rgba(0, 0, 0, 0)), to(rgba(0, 0, 0, .45)))
;
}
CSSSTYLE2;

            echo "<style>".$css."</style>";
        }
    }

    private function RGBtoHSV($R, $G, $B)    // RGB values:    0-255, 0-255, 0-255
    {                                // HSV values:    0-360, 0-100, 0-100
        // Convert the RGB byte-values to percentages
        $R = ($R / 255);
        $G = ($G / 255);
        $B = ($B / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        //   minimum value, and the difference of the two (chroma).
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        //   attempting to calculate it would cause division by zero (see
        //   below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma == 0)
            return array(0, 0, $computedV);

        // Saturation is also simple to compute, and is simply the chroma
        //   over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
        //   the bisecting angle as a value 0 <= x < 6, that represents which
        //   portion of which sector the line falls on.
        if ($R == $minRGB)
            $h = 3 - (($G - $B) / $chroma);
        elseif ($B == $minRGB)
            $h = 1 - (($R - $G) / $chroma);
        else // $G == $minRGB
            $h = 5 - (($B - $R) / $chroma);

        // After we have the sector position, we multiply it by the size of
        //   each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        return array($computedH, $computedS, $computedV);
    }

    private function generate_font_css( $option, $em = '1', $important = false ) {

        // Test if font-face is a Google font
        global $google_fonts;
        foreach ( $google_fonts as $google_font ) {

            // Add single quotation marks to font name and default arial sans-serif ending
            if ( $option['face'] == $google_font['name'] )
                $option['face'] = "'" . $option['face'] . "', arial, sans-serif";

        } // END foreach

        $importantStr = ($important ? " !important" : '');

        if ( !@$option['style'] && !@$option['size'] && !@$option['unit'] && !@$option['color'] )
            return 'font-family: '.stripslashes($option["face"]). $importantStr . ';';
        else {
            if (empty($option['color'])) {
                $color = 'transparent';
            } else {
                $color = $option['color'];
            }
            return 'font:'.$option['style'].' '.$option['size'].$option['unit'].'/'.$em.'em '.stripslashes($option['face']). $importantStr . ';color:'.$color. $importantStr . ';';
        }

    } // End woo_generate_font_css()

	/**
	 * Load any specific custom logic required for the style, if has any.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function load_style_specific_method () {
		$style = $this->get( 'menu_style' );
		$supported_styles = $this->get_menu_styles();

		if ( 'none' != $style ) {
			if (
				isset( $supported_styles[$style]['callback'] ) && 
				'method' == $supported_styles[$style]['callback'] && 
				method_exists( $this, 'style_hooks_' . esc_attr( $style ) )
			) {
				call_user_func( array( $this, 'style_hooks_' . esc_attr( $style ) ) );
			} else {
				if ( isset( $supported_styles[$style]['callback'] ) ) {
					if ( is_callable( $supported_styles[$style]['callback'] ) ) {
						call_user_func( $supported_styles[$style]['callback'] );
					}
				}
			}
		}
	} // End load_style_specific_method()

	/**
	 * Load any specific custom stylesheet required for the style, if has any.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function load_style_specific_stylesheet () {
		$style = $this->get( 'menu_style' );
		$supported_styles = $this->get_menu_styles();

		if ( 'none' != $style ) {
			if (
				isset( $supported_styles[$style]['stylesheet'] ) && 
				'core' == $supported_styles[$style]['stylesheet']
			) {
				wp_enqueue_style( $this->token . '-' . esc_attr( $style ), esc_url( plugins_url( 'styles/' . esc_attr( $style ) . '.css', $this->file ) ) );
			} else {
				if ( isset( $supported_styles[$style]['stylesheet'] ) && '' != $supported_styles[$style]['stylesheet'] ) {
					wp_enqueue_style( $this->token . '-' . esc_attr( $style ), esc_url( $supported_styles[$style]['stylesheet'] ) );
				}
			}
		}
	} // End load_style_specific_stylesheet()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( $this->token, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @access public
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = $this->token;
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->token . '-version', $this->version );
		}
	} // End register_plugin_version()

	/**
	 * Get private variables.
	 * @param   string $var The token for the variable to retrieve.
	 * @access  public
	 * @since   1.0.0
	 * @return  string      The value of the variable retrieved.
	 */
	public function get ( $var ) {
		switch ( $var ) {
			case 'woo_background_image':
				$response = '';
				$bg_image = get_option( 'woo_header_bg_image' );
				if ( '' != $bg_image ) {
					$response = 'background: url(' . esc_url( get_option( 'woo_header_bg_image' ) ) . ');';
					$response .= ' background-color: ' . esc_attr( get_option( 'woo_header_bg' ) ) . ';';
				}
				return $response;
			break;

			case 'woo_header_before':
				return '<div id="header-container" style="' . esc_attr( $this->get( 'woo_background_image' ) ) . '">';
			break;

			case 'woo_header_after':
				return '</div><!--/#header-container-->';
			break;

			case 'woo_nav_before':
				return '<div id="nav-container" style="' . esc_attr( $this->get( 'woo_background_image' ) ) . '">';
			break;

			case 'woo_nav_after':
				return '</div><!--/#nav-container-->';
			break;

			case 'woo_footer_top':
				return '<div id="footer-widgets-container">';
			break;

			case 'woo_footer_before':
				return '</div><!--/#footer-widgets-container-->' . "\n" . '<div id="footer-container">';
			break;

			case 'woo_footer_after':
				return '</div><!--/#footer-container-->';
			break;

			case 'menu_style':

                if ($this->_menu_style == '') {
                    $enabled = get_option('pootlepress-apple-menu-enable', 'true');
                    if ($enabled == '') {
                        $enabled = 'true';
                    }

                    if ($enabled !== 'true') {
                        $this->_menu_style = "none";
                    } else {
                        $this->_menu_style = "apple";
                    }
                }

                return $this->_menu_style;

			break;

			default:
				return false;
			break;
		}
	} // End get()

    /**
     * Load hooks for the "apple" style.
     * @access  private
     * @since   1.0.0
     * @return  void
     */
    private function style_hooks_apple () {
        remove_action('woo_header_inside', 'woo_nav', 10); // this is added by sticky header plugin
        remove_action( 'woo_header_after','woo_nav', 10 );

        $removeLogo = get_option('pootlepress-apple-menu-remove-logo', 'false');
        if ($removeLogo == '') {
            $removeLogo = 'false';
        }

        if ($removeLogo != 'false') {
            remove_action('woo_header_inside', 'woo_logo', 10);
        }


        add_action( 'woo_header_after', array(&$this, 'woo_nav_custom'), 10 );

    } // End style_hooks_header()


    public function woo_nav_custom() {
        global $woo_options;
        woo_nav_before();

        $enabled = get_option('pootlepress-apple-menu-enable', 'true');
        if ($enabled == '') {
            $enabled = 'true';
        }

        if ($enabled == 'true') {

            ?>

            <div id="navigation_apple" class="col-full">
    <!--            --><?php //woo_nav_inside(); ?>

                <?php

                $navSearchEnabled = get_option('woo_nav_search', 'false');
                if ($navSearchEnabled == 'true') {
                    ?>
                    <div id="header-search" class="">
                        <form action="<?php esc_attr_e(home_url()) ?>">
                            <input class="field" type="text" name="s" size="31">
                            <button type="submit" id="go">
                                <span>Go</span>
                                <i class='icon-search'></i>
                            </button>
                        </form>
                    </div>
                    <?php
                }

                ?>


                <?php
                if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( array( 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'main-nav', 'menu_class' => 'nav fl', 'theme_location' => 'primary-menu' ) );
                } else {
                    ?>
                    <ul id="main-nav" class="nav fl">
                        <?php
                        if ( get_option( 'woo_custom_nav_menu' ) == 'true' ) {
                            if ( function_exists( 'woo_custom_navigation_output' ) ) { woo_custom_navigation_output( 'name=Woo Menu 1' ); }
                        } else { ?>

                            <?php if ( is_page() ) { $highlight = 'page_item'; } else { $highlight = 'page_item current_page_item'; } ?>
                            <li class="<?php echo esc_attr( $highlight ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'woothemes' ); ?></a></li>
                            <?php wp_list_pages( 'sort_column=menu_order&depth=6&title_li=&exclude=' ); ?>
                        <?php } ?>
                    </ul><!-- /#nav -->
                <?php } ?>

            </div><!-- /#navigation -->

            <?php
            woo_nav_after();
        }
    } // End woo_nav_custom()

} // End Class


