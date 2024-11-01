<?php
ob_start();
class NICKX_LIC_CLASS {
	public $err;
	private $wp_option = 'nickx_wp_plugin';
	public function is_nickx_act_lic() {
		$nickx_lic = get_option( $this->wp_option );
		if ( ! empty( $nickx_lic ) ) {
			$var_res  = unserialize( base64_decode( $nickx_lic ) );
			$site_url = preg_replace( '#^[^:/.]*[:/]+#i', '', get_site_url() );
			if ( $var_res['d'] > strtotime( date( 'd-m-Y' ) ) && $var_res['rd'] == $site_url ) {
				return true;
			} else {
				return $this->wc_prd_vid_key_srt( $var_res['l'] );
			}
		} else {
			return false;
		}
	}
	public function nickx_act_call( $nickx_lic ) {
		return $this->wc_prd_vid_key_srt( $nickx_lic );
	}
	public function wc_prd_vid_key_srt( $key ) {
		$site_url  = preg_replace( '#^[^:/.]*[:/]+#i', '', get_site_url() );
		$nickx_src = NICKX_PLUGIN_URL . '?slm_action=slm_activate&license_key=' . $key . '&registered_domain=' . $site_url . '&item_reference=wc_product_video_gallery';
		$nickx_res = wp_remote_get( $nickx_src, array( 'timeout' => 20, 'sslverify' => false ) );
		if ( is_array( $nickx_res ) ) {
			$nickx_res      = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', utf8_encode( $nickx_res['body'] ) );
			$nickx_res_data = json_decode( $nickx_res );
			if ( $nickx_res_data->result == 'success' || $nickx_res_data->error_code == 40 || $nickx_res_data->error_code == 110 ) {
				$nickx_key = base64_encode( serialize( array( 'l' => $key, 'rd' => $site_url, 'd' => strtotime( $nickx_res_data->date_of_expiry ), 's' => ( ( isset( $nickx_res_data->error_code ) ) ? $nickx_res_data->error_code : '' ) ) ) );
				update_option( $this->wp_option, $nickx_key );
				return true;
			} else {
				$this->err = $nickx_res_data->message;
				delete_option( $this->wp_option );
				return false;
			}
		}
	}
	public function nickx_deactive() {
		$site_url  = preg_replace( '#^[^:/.]*[:/]+#i', '', get_site_url() );
		$nickx_lic = get_option( $this->wp_option );
		$nickx_lic = unserialize( base64_decode( $nickx_lic ) );
		$deact_url = NICKX_PLUGIN_URL . '?slm_action=slm_deactivate&license_key=' . $nickx_lic['l'] . '&registered_domain=' . $site_url;
		$response  = wp_remote_get( $deact_url, array( 'timeout' => 20, 'sslverify' => false ) );
		if ( is_array( $response ) ) {
			$json = preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', utf8_encode( $response['body'] ) );
			$nickx_res_data = json_decode( $json );
			delete_option( $this->wp_option );
			if ( $nickx_res_data->result == 'success' ) {
				return true;
			} else {
				$this->err = $nickx_res_data->message;
				return false;
			}
		}
	}
}
