<?php

// Modifies the default registration page
function px_verify_view_registration_page() {
	global $errors;
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);

	if($http_post) {
		$action = $_POST['wp-submit'];
		$code = esc_attr($_POST['purchase_code']);
		$verify = px_verify_purchase($code);

		if($action == 'Register') {

			if(!is_wp_error($verify)) {
				$user_login = $_POST['user_login'];
				$user_email = $_POST['user_email'];
				$errors = register_new_user($user_login, $user_email);
				
				if (!is_wp_error($errors)) {
					$user_id = $errors;
					if($verify['px_envato_item'] == 'WordPress Blog Android App') {
						wp_update_user(array('ID' => $user_id, 'role' => 'px_wpba_customer'));
						
					}else if($verify['px_envato_item'] == 'WP Google Cloud Messaging') {
						wp_update_user(array('ID' => $user_id, 'role' => 'px_wpgcm_customer'));
						
					}else {
						wp_update_user(array('ID' => $user_id, 'role' => 'participant'));
					}
					
					update_user_meta( $user_id, 'px_envato_username', $verify['px_envato_username'] );
					update_user_meta( $user_id, 'px_envato_purchase_date', $verify['px_envato_purchase_date'] );
					update_user_meta( $user_id, 'px_envato_purchase_code', $verify['px_envato_purchase_code'] );
					update_user_meta( $user_id, 'px_envato_license', $verify['px_envato_license'] );
					update_user_meta( $user_id, 'px_envato_item', $verify['px_envato_item'] );
					update_user_meta( $user_id, 'px_envato_support_amount', $verify['px_envato_support_amount'] );
					update_user_meta( $user_id, 'px_envato_support_until', $verify['px_envato_support_until'] );

					$redirect_to = 'wp-login.php?checkemail=registered';
					wp_safe_redirect($redirect_to);
					exit();

				}else {
					px_verify_view_registration_form($errors, $verify);
				}
			}else {
				px_verify_view_verification_form($verify);
			}
			
		}elseif ($action == 'Verify') {
		
			if (!is_wp_error($verify)) {
				px_verify_view_registration_form($errors, $verify);
			} else {
				px_verify_view_verification_form($verify);
			}
		}
	} else {
		px_verify_view_verification_form();
	}

	px_verify_custom_style();
	exit();
}

/**
* Verify the code using the envato api
**/
function px_verify_purchase($code, $check = true) {
	$errors = new WP_Error;
	
	if (empty($code)) {
		$errors->add('incomplete_form', '<strong>Error</strong>: Incomplete form fields.');
		return $errors;
	}
	
	$options = get_option( 'px_verifiy_settings' );
	$personal_token = $options[ 'px_verifiy_token' ];
	if ($personal_token == false) {
		$errors->add('incomplete_settings', '<strong>Error</strong>: Please contact admin to setup the plugin settings.');
		return $errors;
	}
	
	$api_url = 'https://api.envato.com/v2/market/author/sale?code=' . $code;
	$verified = false;
	
	// check if purchase code already used
	if($check) {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT umeta.user_id
			FROM $wpdb->usermeta as umeta
			WHERE umeta.meta_value LIKE '%%%s%%' ",
			$code
		);

		$registered = $wpdb->get_var($query);
		if ($registered) {
			$errors->add('used_purchase_code', 'Sorry, but that item purchase code has already been registered with another account. Please login to that account to continue, or create a new account with another purchase code.');
			return $errors;
		}
	}
	
	
	// Send request to envato to verify purchase
	$headers = array(
		'Authorization' => 'Bearer ' . $personal_token
	);
	
	$response = wp_remote_get( $api_url, array('headers' => $headers) );
	$result = '';
	
	if (isset($response['body']) ) {
		$result = json_decode($response['body'], true);
		
		if (isset($result['error']) && isset($result['response_code']) ) {
			$errors->add('invalid_purchase_code', '<strong>Error ' . $result['response_code'] . '</strong>: ' . $result['error']);
			return $errors;
			
		}else if (isset($result['error']) && isset($result['error_description']) ) {
			$errors->add('invalid_purchase_code', '<strong>' . $result['error'] . '</strong>: ' . $result['error_description']);
			return $errors;
		
		}else if (isset($result['error']) && isset($result['description']) ) {
			$errors->add('invalid_purchase_code', '<strong>' . $result['error'] . '</strong>: ' . $result['description']);
			return $errors;
			
		}else {
			$verify = array(
				'px_envato_username' => $result['buyer'],
				'px_envato_purchase_date' => $result['sold_at'],
				'px_envato_purchase_code' => $code,
				'px_envato_license' => $result['license'],
				'px_envato_item' => $result['item']['name'],
				'px_envato_support_amount' => $result['support_amount'],
				'px_envato_support_until' => $result['supported_until'],
			);
			
			return $verify;		
		}
	}else {
		$errors->add('invalid_api_response', '<strong>Invalid response from the API</strong>');
		return $errors;
	}
}



/**
* visual things
**/
function px_verify_view_verification_form($errors = '') {	
	login_header(__('Verify Purchase Form'), '<p class="message register">' . __('Verify Purchase') . '</p>', $errors); ?>

	<form name="registerform" id="registerform" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>" method="post">
		<p>
			<label for="purchase_code"><?php _e('Purchase Code') ?><br />
			<input type="text" name="purchase_code" id="purchase_code" class="input" size="50" tabindex="20" /></label>
			<p><a href="<?php echo  plugins_url(); ?>/pixelart-verifier/img/find-item-purchase-code.png" target="_blank">Where can I find my item purchase code?</a></p>
		</p>
		<br class="clear" />
		<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Verify'); ?>" tabindex="100" /></p>
	</form>
	
	<p>Created with love by <a href="http://codecanyon.net/user/PixelartDev?ref=PixelartDev">Pixelart Web and App Development</a></p>

	<?php
	login_footer('user_login');

}


function px_verify_view_registration_form( $errors = '', $verified = array() ) {
	login_header(__('Registration Form'), '<p class="message register">' . __('Register An Account') . '</p>', $errors);

	if ($verified) {
		?>
		<div class="message success">
			<h3>Purchase Information</h3><br/>
			<p><strong>Buyer: </strong><?php echo $verified['px_envato_username']; ?></p>
			<p><strong>Item: </strong><?php echo $verified['px_envato_item']; ?></p>
			<p><strong>License: </strong><?php echo $verified['px_envato_license']; ?></p>
			<p><strong>Purchase Code: </strong><?php echo $verified['px_envato_purchase_code']; ?></p>
		</div>
		<?php
	}

	?>

	<form name="registerform" id="registerform" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>" method="post">
		<input type="hidden" name="purchase_code" value="<?php echo $verified['px_envato_purchase_code']; ?>" />

		<p>
			<label for="user_login"><?php _e('Username') ?><br />
			<input type="text" name="user_login" id="user_login" class="input" value="<?php echo $verified['px_envato_username']; ?>" size="20" tabindex="10" /></label>
		</p>
		<p>
			<label for="user_email"><?php _e('E-mail') ?><br />
			<input type="email" name="user_email" id="user_email" class="input" value="" size="25" tabindex="20" /></label>
		</p>

		<p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
		<br class="clear" />
		<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register'); ?>" tabindex="100" /></p>
	</form>

	<p id="nav">
	<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e( 'Log in' ); ?></a> |
	<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found' ) ?>"><?php _e( 'Lost your password?' ); ?></a>
	</p>

	<?php
	login_footer('user_login');
}

	
/** 
* Helpfull Functions 
**/
function px_verify_custom_style() {
	$options = get_option( 'px_verifiy_settings' );
	$style = isset($options['px_verifiy_css']) ? $options['px_verifiy_css'] : '#login {width: 500px} .success {background-color: #F0FFF8; border: 1px solid #CEEFE1;';

	if (!empty($style)) {
		echo '<style>';
			echo $style;
		echo '</style>';
	}
}

function px_verify_shaker( $shake_error_codes ) {
	$extras = array('invalid_purchase_code', 'invalid_api_response', 'incomplete_form', 'incomplete_settings', 'used_purchase_code');
	$shake_error_codes = array_merge($extras, $shake_error_codes);
	return $shake_error_codes;
}

function px_verify_modify_login_headerurl($login_header_url = null) {
	$login_header_url = site_url();
	return $login_header_url;
}



add_action( 'login_form_register', 'px_verify_view_registration_page');
add_filter( 'shake_error_codes', 'px_verify_shaker', 10, 1 );
add_filter( 'login_headerurl', 'px_verify_modify_login_headerurl', 10, 1);

?>