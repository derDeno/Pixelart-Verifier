<?php

/** The admin side functions **/
global $pagenow;

function px_verifiy_add_admin_menu() {
	add_options_page( 'Pixelart Verifier', 'Pixelart Verifier', 'manage_options', 'px_verifier', 'px_verifiy_options_page' );
}


function px_verifiy_settings_init() {

	register_setting( 'Pixelart Verifier', 'px_verifiy_settings' );

	add_settings_section(
		'px_verifiy_settings_section',
		'',
		'',
		'Pixelart Verifier'
	);

	add_settings_field(
		'px_verifiy_token',
		__( 'Personal Token', 'px_verify' ),
		'px_verifiy_token_render',
		'Pixelart Verifier',
		'px_verifiy_settings_section'
	);

	add_settings_field(
		'px_verifiy_cb_badge',
		__( 'Show bbPress Badge', 'px_verify' ),
		'px_verifiy_cb_badge_render',
		'Pixelart Verifier',
		'px_verifiy_settings_section'
	);

	add_settings_field(
		'px_verifiy_css',
		__( 'Custom CSS', 'px_verify' ),
		'px_verifiy_css_render',
		'Pixelart Verifier',
		'px_verifiy_settings_section'
	);


}


function px_verifiy_token_render() {
	$options = get_option( 'px_verifiy_settings' );
	?>
	<input type='text' name='px_verifiy_settings[px_verifiy_token]' value='<?php echo $options['px_verifiy_token']; ?>' size='40'  />
	<p class="description"><a href="<?php echo plugins_url(); ?>/pixelart-verifier/img/get-personal-token.png" target="_blank"><?php _e('How to obtain a Personal Token', 'px_verify'); ?></a></p>
	<?php
}


function px_verifiy_cb_badge_render() {
	$options = get_option( 'px_verifiy_settings' );

	if ( !isset($options['px_verifiy_cb_badge']) ) {
		$options['px_verifiy_cb_badge'] = 0;
	}
	?>
	<input type='checkbox' name='px_verifiy_settings[px_verifiy_cb_badge]' value='1' <?php checked($options['px_verifiy_cb_badge'], 1); ?> />
	<?php
}


function px_verifiy_css_render() {
	$options = get_option('px_verifiy_settings');
	$style = empty($options['px_verifiy_css']) ? $options['px_verifiy_css'] : '#login {width: 500px} .success {background-color: #F0FFF8; border: 1px solid #CEEFE1;';
	?>
	<textarea cols='70' rows='5' name='px_verifiy_settings[px_verifiy_css]'><?php echo $style; ?></textarea>
	<?php
}


function px_verifiy_options_page() {
	$result = '';
	if (isset($_GET['action']) && $_GET['action'] == 'refreshEnvatoData') {
		$result = px_verify_refresh_envato_data($user_id);
	}else if (isset($_GET['action']) && $_GET['action'] == 'upgradeAqua') {
		$result = px_verify_upgrade_aqua($user_id);
	}

	?>
	<form action='options.php' method='post'>
		<h2>Pixelart Verifier</h2>
		<?php
		settings_fields('Pixelart Verifier');
		do_settings_sections('Pixelart Verifier');
		submit_button();
		?>
	</form>
	<?php
}


function px_verify_powered_by() {
	return 'Powered by <img class="" width="200px" src="' .  plugins_url() . '/pixelart-verifier/img/envato-api-logo.png"> </img>';
}

function px_verify_copyright () {
	return 'Created with love by <a href="http://codecanyon.net/user/PixelartDev?ref=PixelartDev" target="_blank"> <img class="" width="200px" src="' .  plugins_url() . '/pixelart-verifier/img/pixelart-logo.png"></img></a>';
}

if ($pagenow == 'options-general.php' && $_GET['page'] == 'px_verifier') {
	add_filter('admin_footer_text', 'px_verify_copyright', 99);
	add_filter( 'update_footer', 'px_verify_powered_by', 99);
}

add_action('admin_menu', 'px_verifiy_add_admin_menu');
add_action('admin_init', 'px_verifiy_settings_init');

?>
