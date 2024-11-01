<?php

/**
 * 3D Product Viewer & WebAR for WooCommerce
 *
 * @package       PRODUCT_VIEWER
 * @author        Virakle
 * @version       1.0.5
 *
 * @wordpress-plugin
 * Plugin Name:   3D Product Viewer & WebAR for WooCommerce
 * Plugin URI:    https://viraview.virakle.nl/
 * Description:   Boost sales met 3D & Augmented Reality Bevorder jouw klantbinding met behulp van het Viraview platform.
 * Version:       1.0.5
 * Author:        Virakle
 * Author URI:    https://viraview.virakle.nl/
 * Text Domain:   3d-product-viewer-webar-for-woocommerce
 * Domain Path:   /languages
 * WC requires at least: 2.3
 * WC tested up to: 7.9.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('NICKX_PLUGIN_URL', '');
define('NICKX_PLUGIN_VERSION', '1.0');

require_once plugin_dir_path(__FILE__) . 'js/nickx_live.php';

/**
	Activation
 */
function nickx_activation_hook_callback()
{
	set_transient('nickx-plugin_setting_notice', true, 0);
	if (empty(get_option('nickx_slider_layout'))) {
		update_option('nickx_slider_layout', 'horizontal');
		update_option('nickx_sliderautoplay', 'no');
		update_option('nickx_arrowinfinite', 'yes');
		update_option('nickx_arrowdisable', 'yes');
		update_option('nickx_arrow_thumb', 'no');
		update_option('nickx_hide_thumbnails', 'no');
		update_option('nickx_gallery_action', 'no');
		update_option('nickx_adaptive_height', 'no');
		update_option('nickx_place_of_the_video', 'no');
		update_option('nickx_rtl', 'no');
		update_option('nickx_videoloop', 'no');
		update_option('nickx_vid_autoplay', 'no');
		update_option('nickx_template', 'no');
		update_option('nickx_controls', 'yes');
		update_option('nickx_show_lightbox', 'yes');
		update_option('nickx_show_zoom', 'yes');
		update_option('nickx_show_only_video', 'no');
		update_option('nickx_thumbnails_to_show', 4);
		update_option('nickx_arrowcolor', '#000');
		update_option('nickx_arrowbgcolor', '#FFF');
	}
}

register_activation_hook(__FILE__, 'nickx_activation_hook_callback');

/**
	Settings Class
 */
class WC_PRODUCT_VIDEO_GALLERY
{
	/** @var $extend Lic value */
	public $extend;

	function __construct()
	{
		$this->add_actions(new NICKX_LIC_CLASS());
	}
	private function add_actions($extend)
	{
		$this->extend = $extend;
		add_action('admin_notices', array($this, 'nickx_notice_callback_notice'));
		add_action('admin_menu', array($this, 'wc_product_iframe_gallery_setup'));
		add_action('admin_init', array($this, 'update_wc_product_video_gallery_options'));

		add_action('wp_enqueue_scripts', array($this, 'nickx_enqueue_scripts'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'wc_prd_vid_slider_settings_link'));
		add_shortcode('product_gallery_shortcode', array($this, 'product_gallery_shortcode_callback'));
		add_filter('wc_get_template', array($this, 'nickx_get_template'), 99, 5);
	}
	public function nickx_notice_callback_notice()
	{
		if (get_transient('nickx-plugin_setting_notice')) {
			echo '<div class="notice-info notice is-dismissible"><p><strong>WC product 3d viewer is almost ready.</strong> To Complete Your Configuration, <a href="' . esc_url(admin_url()) . '?page=3d-viewer-ar-instructions">Complete the setup</a>.</p></div>';
			delete_transient('nickx-plugin_setting_notice');
		}
	}
	public function wc_product_iframe_gallery_setup()
	{
		add_submenu_page('edit.php?post_type=product', '3D Product Viewer', '3D Product Viewer', 'manage_options', 'wc-product-iframe', array($this, 'wc_product_iframe_callback'));
		add_menu_page(
			'3D Product Viewer',
			'3D Product Viewer',
			'manage_options',
			'3d-viewer-ar-instructions',
			array($this, 'wc_product_iframe_callback'),
			plugin_dir_url(__FILE__) . 'images/icon.png',
			35
		);
	}

	public function wc_product_iframe_callback()
	{
		if (isset($_POST['wc_product_iframe_token']) && !empty($_POST['wc_product_iframe_token'])) {
			$wc_product_iframe_token = sanitize_text_field($_POST['wc_product_iframe_token']);
			update_option('wc_product_iframe_token', $wc_product_iframe_token);
		}
		echo '
			<style>
				body {
					font-family: Arial, sans-serif;
				}
				.flex-container {
					display: flex;
					flex-wrap: wrap;
					justify-content: center;
					align-items: center;
					text-align: center;
				}
				.flex-container > div {
					width: 80%;
					margin: 20px;
					padding: 20px;
					border: 1px solid #ddd;
					border-radius: 10px;
					box-shadow: 0 0 10px rgba(0,0,0,0.1);
				}
				.flex-container h1 {
					color: #444;
				}
				.flex-container h4 {
					color: #666;
					font-size: 1.2em;
				}
				.flex-container p {
					color: #777;
					font-size: 1.1em;
				}
				.flex-container ol {
					margin-left: 20px;
					color: #666;
				}
				.flex-container a {
					color: #0066cc;
				}
				</style>

				<img src="' . plugin_dir_url(__FILE__) . 'images/logo-virakle.png' . '" style="
					display: block;
					margin-left: auto;
					margin-right: auto;
					width: 200px;
					padding-top: 20px;">
				<div class="flex-container">
					<div>
						<h1>Get Started with Viraview</h1>
						<form action="" method="post">
							<label>Enter your authorization token here:</label><br>
							<input type="text" name="wc_product_iframe_token" value="' . get_option('wc_product_iframe_token') . '">
							<input type="submit" class="button" value="Submit">
						</form>

						<h4>Your journey to a user-friendly 3D/WebAR experience starts here.</h4>
						<p>Follow these easy steps to add your first 3D product:</p>
						<ol>
							<li>Start by creating an account at the Viraview Platform. Use this <a target="_blank" href="https://portal.virakle.nl/">link</a> to get started.</li>
							<li>Once you have created an account, you will need to fill in some details.</li>
							<li>Then you can a new 3D product to your portal.</li>
							<li>Find your access token on the homepage, copy it, and paste it into the field above.</li>
							<li>Proceed to add the 3D product to your own WooCommerce product through the edit/add option inside the Wordpress Website.</li>
							<li>Voila! Your 3D model is now accessible on your frontend in the carousel.</li>
						</ol>
					</div>
				</div>
			';
	}

	public function product_gallery_shortcode_callback($atts = array())
	{
		ob_start();
		echo '<span id="product_gallery_shortcode">';
		$lic_chk_stateus = $this->extend->is_nickx_act_lic();
		if ($lic_chk_stateus) {
			nickx_show_product_image();
		} else {
			echo 'To use shortcode you need to activate license key...!!';
		}
		echo '</span>';
		return ob_get_clean();
	}
	public function nickx_get_template($located, $template_name, $args, $template_path, $default_path)
	{
		if (is_product() && 'single-product/product-image.php' == $template_name && get_option('nickx_template') == 'yes') {
			return nickx_show_product_image();
		}
		return $located;
	}

	public function update_wc_product_video_gallery_options($value = '')
	{
		register_setting('wc_product_video_gallery_options', 'nickx_slider_layout');
		register_setting('wc_product_video_gallery_options', 'nickx_sliderautoplay');
		register_setting('wc_product_video_gallery_options', 'nickx_arrowinfinite');
		register_setting('wc_product_video_gallery_options', 'nickx_arrowdisable');
		register_setting('wc_product_video_gallery_options', 'nickx_arrow_thumb');
		register_setting('wc_product_video_gallery_options', 'nickx_show_lightbox');
		register_setting('wc_product_video_gallery_options', 'nickx_show_zoom');
		register_setting('wc_product_video_gallery_options', 'nickx_arrowcolor');
		register_setting('wc_product_video_gallery_options', 'nickx_show_only_video');
		register_setting('wc_product_video_gallery_options', 'custom_icon');
		register_setting('wc_product_video_gallery_options', 'nickx_hide_thumbnails');
		register_setting('wc_product_video_gallery_options', 'nickx_gallery_action');
		register_setting('wc_product_video_gallery_options', 'nickx_template');
		register_setting('wc_product_video_gallery_options', 'nickx_thumbnails_to_show');
		register_setting('wc_product_video_gallery_options', 'nickx_rtl');
		register_setting('wc_product_video_gallery_options', 'nickx_arrowbgcolor');
		if ($this->extend->is_nickx_act_lic()) {
			register_setting('wc_product_video_gallery_options', 'nickx_adaptive_height');
			register_setting('wc_product_video_gallery_options', 'nickx_videoloop');
			register_setting('wc_product_video_gallery_options', 'nickx_vid_autoplay');
			register_setting('wc_product_video_gallery_options', 'nickx_controls');
			register_setting('wc_product_video_gallery_options', 'nickx_place_of_the_video');
		}
	}

	public function wc_prd_vid_slider_settings_link($links)
	{
		$links[] = '<a href="' . esc_url(admin_url()) . 'edit.php?post_type=product&page=wc-product-video">Settings</a>';
		return $links;
	}

	public function nickx_enqueue_scripts()
	{
		if (!is_admin()) {
			if (class_exists('WooCommerce') || is_product() || is_page_template('page-templates/template-products.php')) {
				wp_enqueue_script('jquery');
				if (get_option('nickx_show_lightbox') == 'yes') {
					wp_enqueue_script('nickx-fancybox-js', plugins_url('js/jquery.fancybox.js', __FILE__), array('jquery'), NICKX_PLUGIN_VERSION, true);
					wp_enqueue_style('nickx-fancybox-css', plugins_url('css/fancybox.css', __FILE__), '3.5.7', true);
				}
				if (get_option('nickx_show_zoom') != 'off') {
					wp_enqueue_script('nickx-zoom-js', plugins_url('js/jquery.zoom.min.js', __FILE__), array('jquery'), '1.7.3', true);
					wp_enqueue_script('nickx-elevatezoom-js', plugins_url('js/jquery.elevatezoom.min.js', __FILE__), array('jquery'), '3.0.8', true);
				}
				wp_enqueue_style('nickx-fontawesome-css', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', '1.0', true);
				wp_enqueue_style('nickx-front-css', plugins_url('css/nickx-front.css', __FILE__), NICKX_PLUGIN_VERSION, true);
				wp_register_script('nickx-front-js', plugins_url('js/nickx.front.js', __FILE__), array('jquery'), NICKX_PLUGIN_VERSION, true);
				$video_type = get_post_meta(get_the_ID(), '_nickx_product_video_type', true);
				if ((is_array($video_type) && in_array('nickx_video_url_vimeo', get_post_meta(get_the_ID(), '_nickx_product_video_type', true))) || get_post_meta(get_the_ID(), '_nickx_product_video_type', true) == 'nickx_video_url_vimeo') {
					wp_enqueue_script('nickx-vimeo-js', 'https://player.vimeo.com/api/player.js', '1.0', true);
				}
				wp_enqueue_style('dashicons');
				$options           = get_option('nickx_options');
				$translation_array = array(
					'nickx_slider_layout'      => get_option('nickx_slider_layout'),
					'nickx_sliderautoplay'     => get_option('nickx_sliderautoplay'),
					'nickx_rtl'                => get_option('nickx_rtl'),
					'nickx_arrowinfinite'      => get_option('nickx_arrowinfinite'),
					'nickx_arrowdisable'       => get_option('nickx_arrowdisable'),
					'nickx_arrow_thumb'        => get_option('nickx_arrow_thumb'),
					'nickx_hide_thumbnails'    => get_option('nickx_hide_thumbnails'),
					'nickx_thumbnails_to_show' => get_option('nickx_thumbnails_to_show', 4),
					'nickx_show_lightbox'      => get_option('nickx_show_lightbox'),
					'nickx_show_zoom'          => get_option('nickx_show_zoom'),
					'nickx_arrowcolor'         => get_option('nickx_arrowcolor'),
					'nickx_arrowbgcolor'       => get_option('nickx_arrowbgcolor'),
					'nickx_lic'                => $this->extend->is_nickx_act_lic(),
				);
				if ($this->extend->is_nickx_act_lic()) {
					$translation_array['nickx_adaptive_height']    = get_option('nickx_adaptive_height');
					$translation_array['nickx_place_of_the_video'] = get_option('nickx_place_of_the_video');
					$translation_array['nickx_videoloop']          = get_option('nickx_videoloop');
					$translation_array['nickx_vid_autoplay']       = get_option('nickx_vid_autoplay');
				}
				wp_localize_script('nickx-front-js', 'wc_prd_vid_slider_setting', $translation_array);
				wp_enqueue_script('nickx-front-js');
			}
		}

		wp_enqueue_style('custom-style-css', plugins_url('css/custom-style.css', __FILE__), '1.0.1', true);
	}
}
function nickx_error_notice_callback_notice()
{
	echo '<div class="error"><p><strong>Product Video Gallery for Woocommerce</strong> requires WooCommerce to be installed and active. You can download <a href="https://woocommerce.com/" target="_blank">WooCommerce</a> here.</p></div>';
}
add_action('plugins_loaded', 'nickx_remove_woo_hooks');
function nickx_remove_woo_hooks()
{
	if (!function_exists('is_plugin_active_for_network')) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}
	if ((is_multisite() && is_plugin_active_for_network('woocommerce/woocommerce.php')) || is_plugin_active('woocommerce/woocommerce.php')) {
		new WC_PRODUCT_VIDEO_GALLERY();
		remove_action('woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_thumbnails', 20);
		remove_action('woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20);
		if (get_option('nickx_hide_thumbnails') != 'yes') {
			add_action('woocommerce_product_thumbnails', 'nickx_show_product_thumbnails', 20);
		}
		if (get_option('nickx_gallery_action') != 'yes') {
			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10);
			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
			add_action('woocommerce_before_single_product_summary', 'nickx_show_product_image', 10);
		}
	} else {
		add_action('admin_notices', 'nickx_error_notice_callback_notice');
	}
}

function nickx_get_nickx_iframe_html($product_video_url, $extend, $key = 1)
{

	return '<div class="tc_video_slide"><iframe id="nickx_yt_video_' . $key . '" style="display:none;" data-skip-lazy="" width="100%" height="100%" class="product_video_iframe" data_src="' . esc_url($product_video_url) . '" src="' . esc_url($product_video_url) . '" frameborder="0" allow="autoplay; accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
}
function nickx_show_product_image()
{
	global $post, $product, $woocommerce;
	$product_video_urls = get_post_meta(get_the_ID(), '_custom_api_product_url_', true);


	$extend = new NICKX_LIC_CLASS();
	echo '<div class="images nickx_product_images_with_video loading">';
	if (has_post_thumbnail() || !empty($product_video_urls)) {
		$attachment_ids    = ($product) ? $product->get_gallery_image_ids() : '';
		$imgfull_src       = get_the_post_thumbnail_url(get_the_ID(), 'full');
		$htmlvideo         = '';
		if (!empty($product_video_urls)) {
			if (is_array($product_video_urls)) {
				foreach ($product_video_urls as $key => $product_video_url) {
					if (!empty($product_video_url)) {
						$htmlvideo .= nickx_get_nickx_iframe_html($product_video_url, $extend, $key);
					}
					if (!$extend->is_nickx_act_lic()) {
						break;
					}
				}
			} else {
				$htmlvideo .= nickx_get_nickx_iframe_html($product_video_urls, $extend);
			}
		}



		$product_image = get_the_post_thumbnail($post->ID, 'woocommerce_single', array('data-skip-lazy' => 'true', 'data-zoom-image' => $imgfull_src));
		$html = '<div class="slider nickx-slider-for">';
		if (get_option('nickx_show_only_video') == 'yes' && $extend->is_nickx_act_lic()) {
			$html .= $htmlvideo;
		} else {
			$html .= ((get_option('nickx_place_of_the_video') == 'yes' && $extend->is_nickx_act_lic()) ? $htmlvideo : '');

			$html .= ((get_option('nickx_place_of_the_video') == 'second' && $extend->is_nickx_act_lic()) ? $htmlvideo : '');
			foreach ($attachment_ids as $attachment_id) {
				$imgfull_src = wp_get_attachment_image_url($attachment_id, 'full');
				$html       .= '<div class="zoom">' . wp_get_attachment_image($attachment_id, 'woocommerce_single', 0, array('data-skip-lazy' => 'true', 'data-zoom-image' => $imgfull_src)) . '</div>';
			}
			$html .= ((get_option('nickx_place_of_the_video') == 'no' && get_option('nickx_place_of_the_video') != 'yes' &&  get_option('nickx_place_of_the_video') != 'second' || !$extend->is_nickx_act_lic()) ? $htmlvideo : '');
		}
		$html .= '</div>';
		echo apply_filters('woocommerce_single_product_image_html', $html, $post->ID);
	} else {
		echo apply_filters('woocommerce_single_product_image_html', sprintf('<img data-skip-lazy="" src="%s" alt="%s" />', wc_placeholder_img_src(), __('Placeholder', 'woocommerce')), $post->ID);
	}
	do_action('woocommerce_product_thumbnails');
	echo '</div>';
}
function nickx_get_video_thumbanil_html($post, $thumbnail_size)
{
	$gallery_thumbnail_size = wc_get_image_size($thumbnail_size);
	$product_video_urls = get_post_meta(get_the_ID(), '_custom_api_product_url_', true);
	if (!empty($product_video_urls)) {
		$product_video_thumb_ids  = get_post_meta(get_the_ID(), '_product_video_thumb_url', true);
		$custom_thumbnails        = get_post_meta(get_the_ID(), '_custom_thumbnail', true);
		$vide_custom_thumbnails        = 'https://portal.virakle.nl/storage/products/image/' . get_post_meta(get_the_ID(), '_custom_api_product_thumbnail_', true);
		if (is_array($product_video_urls)) {
			$extend = new NICKX_LIC_CLASS();
			foreach ($product_video_urls as $key => $product_video_url) {
				if (!empty($product_video_url)) {
					$product_video_thumb_id   = isset($product_video_thumb_ids[$key]) ? $product_video_thumb_ids[$key] : '';
					$custom_thumbnail        = isset($custom_thumbnails[$key]) && !empty($product_video_thumb_id) ? $custom_thumbnails[$key] : '';
					$product_video_thumb_url = wc_placeholder_img_src();
					if ($product_video_thumb_id) {
						$product_video_thumb_url = wp_get_attachment_image_url($product_video_thumb_id, $thumbnail_size);
					} elseif (get_option('custom_icon')) {
						$custom_thumbnail        = 'yes';
						$product_video_thumb_url = get_option('custom_icon');
					}
					echo apply_filters('woocommerce_single_product_image_thumbnail_html', '<li title="video" class="video-thumbnail"><img width="' . $gallery_thumbnail_size['width'] . '" height="' . $gallery_thumbnail_size['height'] . '" data-skip-lazy="" global-thumb="' . esc_url(get_option('custom_icon')) . '" src="' . esc_url($vide_custom_thumbnails) . '" custom_thumbnail="' . esc_attr($vide_custom_thumbnails) . '" class="product_video_img img_' . $key . ' attachment-thumbnail size-thumbnail" alt="" sizes="(max-width: 150px) 100vw, 150px"></li>', '', $post->ID);
					if (!$extend->is_nickx_act_lic()) {
						break;
					}
				}
			}
		} else {
			$product_video_thumb_urls = wc_placeholder_img_src();
			if ($product_video_thumb_ids) {
				$product_video_thumb_urls = wp_get_attachment_image_url($product_video_thumb_ids, $thumbnail_size);
			} elseif (get_option('custom_icon')) {
				$custom_thumbnails        = 'yes';
				$product_video_thumb_urls = get_option('custom_icon');
			}
			echo apply_filters('woocommerce_single_product_image_thumbnail_html', '<li title="video" class="video-thumbnail"><img width="' . $gallery_thumbnail_size['width'] . '" height="' . $gallery_thumbnail_size['height'] . '" data-skip-lazy="" global-thumb="' . esc_url(get_option('custom_icon')) . '" src="' . esc_url($vide_custom_thumbnails) . '" custom_thumbnail="' . esc_attr($vide_custom_thumbnails) . '" class="product_video_img img_0 attachment-thumbnail size-thumbnail" alt="" sizes="(max-width: 150px) 100vw, 150px"></li>', '', $post->ID);
		}
	} else {
		return;
	}
}
function nickx_show_product_thumbnails()
{
	global $post, $product, $woocommerce;
	$extend         = new NICKX_LIC_CLASS();
	$attachment_ids = $product->get_gallery_image_ids();
	if (has_post_thumbnail()) {
		$thumbanil_id   = array(get_post_thumbnail_id());
		$attachment_ids = array_merge($thumbanil_id, $attachment_ids);
	}
	$thumbnail_size    = apply_filters('woocommerce_gallery_thumbnail_size', 'woocommerce_gallery_thumbnail');
	if (($attachment_ids && $product->get_image_id()) || !empty(get_post_meta(get_the_ID(), '_custom_api_product_url_', true))) {
		echo '<div id="nickx-gallery" class="slider nickx-slider-nav">';
		if ((get_option('nickx_show_only_video') == 'yes' && $extend->is_nickx_act_lic()) || empty($attachment_ids)) {
			nickx_get_video_thumbanil_html($post, $thumbnail_size);
		} else {
			if (get_option('nickx_place_of_the_video') == 'yes' && $extend->is_nickx_act_lic()) {
				nickx_get_video_thumbanil_html($post, $thumbnail_size);
			}
			foreach ($attachment_ids as $attachment_id) {
				$props = wc_get_product_attachment_props($attachment_id, $post);
				if (!$props['url']) {
					continue;
				}
				echo apply_filters('woocommerce_single_product_image_thumbnail_html', sprintf('<li class="product_thumbnail_item ' . (($thumbanil_id[0] == $attachment_id) ? 'wp-post-image-thumb' : '') . '" title="%s">%s</li>', esc_attr($props['caption']), wp_get_attachment_image($attachment_id, $thumbnail_size, 0, array('data-skip-lazy' => 'true'))), $attachment_id);
				if ($thumbanil_id[0] == $attachment_id && get_option('nickx_place_of_the_video') == 'second' && $extend->is_nickx_act_lic()) {
					nickx_get_video_thumbanil_html($post, $thumbnail_size);
				}
			}
			if (get_option('nickx_place_of_the_video') == 'no' && get_option('nickx_place_of_the_video') != 'yes' && get_option('nickx_place_of_the_video') != 'second' || !$extend->is_nickx_act_lic()) {
				nickx_get_video_thumbanil_html($post, $thumbnail_size);
			}
		}
		echo '</div>';
	}
}


function woocommerce_product_custom_fields1()
{
	global $woocommerce, $post;
	$url = 'https://portal.virakle.nl/api/products';
	$token = get_option('wc_product_iframe_token');
	$headers = [
		'Authorization' => 'Bearer ' . $token,
		'Content-Type' => 'application/json'
	];
	$wp_get_post_response = wp_remote_request(
		$url,
		array(
			'method'    => 'GET',
			'headers'   => $headers
		)
	);
	if (is_wp_error($wp_get_post_response)) {
		echo 'An error happened';
	} else {
		$body = wp_remote_retrieve_body($wp_get_post_response);
		$data = json_decode($body);
	}

	$options_title_110 = array('' => __('Select Option', 'woocommerce'));
	foreach ($data as $opt) {
		$options_title_110[$opt->id] = __($opt->product, 'woocommerce');
	}

	echo '<div class="product_custom_field">';
	echo '<img src="' . plugin_dir_url(__FILE__) . 'images/viraview.png' . '" style="max-width: 200px;">';
	// Custom Product Text Field
	woocommerce_wp_select(array(
		'id'          => '_custom_api_product_id_',
		'label'       => __('Select a 3D product', 'woocommerce'),
		'description' => __('Select a 3D model from the list, this 3D model will show up in the carrousel on the front end!', 'woocommerce'),
		'desc_tip'    => true,
		'options'     => $options_title_110
	));
	echo '</div>';
}

function woocommerce_product_custom_fields_save($post_id)
{
	// Custom Product Text Field
	$woocommerce_custom_api_product_ = sanitize_text_field($_POST['_custom_api_product_id_']);
	if (!empty($woocommerce_custom_api_product_))
		$url = 'https://portal.virakle.nl/api/object/' . $woocommerce_custom_api_product_;
	$token = get_option('wc_product_iframe_token');
	$headers = [
		'Authorization' => 'Bearer ' . $token,
		'Content-Type' => 'application/json'
	];
	$wp_get_post_response = wp_remote_request(
		$url,
		array(
			'method'    => 'GET',
			'headers'   => $headers
		)
	);
	if (is_wp_error($wp_get_post_response)) {
		echo 'An error happened';
	} else {
		$body = wp_remote_retrieve_body($wp_get_post_response);
		$data = json_decode($body);
	}
	$iframe_url = 'https://portal.virakle.nl/public/viraview/ar/' . $data->url;
	$woocommerce_custom_api_product_ = esc_attr($woocommerce_custom_api_product_);
	$data_img = esc_attr($data->image);
	$woocommerce_custom_api_product_ = esc_attr($woocommerce_custom_api_product_);
	update_post_meta($post_id, '_custom_api_product_id_', $woocommerce_custom_api_product_);
	update_post_meta($post_id, '_custom_api_product_url_', ($iframe_url));
	update_post_meta($post_id, '_custom_api_product_thumbnail_', $data_img);
}

add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields1');
// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
