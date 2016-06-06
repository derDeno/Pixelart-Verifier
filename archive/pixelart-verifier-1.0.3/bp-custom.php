<?php


$options = get_option('px_verifiy_settings');
if (isset($options['px_verifiy_cb_badge']) && $options['px_verifiy_cb_badge'] == 1) {
	add_action('bbp_theme_after_reply_author_details', 'bbp_display_badge', 1);
}

/**
* Display the Support status of an User under his replies or topics in the Forum
**/
function bbp_display_badge() {	
	$user_id = bbp_get_reply_author_id();
	
	/*$caps = get_user_meta($user_id, 'wp_capabilities', true);
	$roles = array_keys((array)$caps);
	
	if (strtolower($roles[0]) == 'keymaster' || strtolower($roles[0]) == 'administrator' || strtolower($roles[0]) == 'moderator') {
		return;
	}
	
	*/
	
	$dat = get_userdata($user_id);
	$roles = implode(', ', $dat->roles);
	
	if ( strpos(strtolower($roles), 'keymaster') !== false || strpos(strtolower($roles), 'administrator') !== false || strpos(strtolower($roles), 'moderator') !== false) {
		return;
	}
	
	px_verify_badge_style();
	
	$buy_date = get_user_meta( $user_id, 'px_envato_purchase_date');
//	$support_amount = get_user_meta( $user_id, 'px_envato_support_amount'); 	NOT WORKING YET
//	$support_until = get_user_meta( $user_id, 'px_envato_support_until');
	
	// Calculate the if support is valid
	if (isset($buy_date[0]) && $buy_date[0] != 0) {
		$short_date = explode('T', $buy_date[0]);
		$short_date = $short_date[0];
		
		$date1 = new DateTime($short_date);
		$date2 = new DateTime( current_time('Y-m-d') );
		$interval = $date1->diff($date2);
		
		if ($interval->days <= 182) {
			$supported = 1;
		}else {
			$supported = 2;
		}
	}else if (strpos(strtolower($roles), 'blocked') !== false) {
		$supported = 3;
	}else {
		$supported = 4;
	}
	
	if ($supported == 1) {
		?>
		<div id="user-badge-<?php echo $user_id; ?>" class="badge-supported">
			Supported
		</div>
		<?php
		
	}else if ($supported == 2) {
		?>
		<div id="user-badge-<?php echo $user_id; ?>" class="badge-unsupported">
			Support Expired
		</div>
		<?php
	}else if($supported == 3) {
		?>
		<div id="user-badge-<?php echo $user_id; ?>" class="badge-blocked">
			Blocked
		</div>
		<?php
	}else if($supported == 4) {
		?>
		<div id="user-badge-<?php echo $user_id; ?>" class="badge-unknowen">
			Unknowen Support Status
		</div>
		<?php
	}
}

// Badge CSS style
function px_verify_badge_style() {
	?>
	<style>
		.badge-supported {
			border-radius: 5px;
			color: #FFF;
			background: #27ae60;
		}
		
		.badge-unsupported {
			border-radius: 5px;
			color: #FFF;
			background: #7f8c8d;
		}
		
		.badge-blocked {
			border-radius: 5px;
			color: #FFF;
			background: #e74c3c;
		}
		
		.badge-unknowen {
			border-radius: 5px;
			color: #FFF;
			background: #e67e22;
		}
	</style>
	<?php
}

?>