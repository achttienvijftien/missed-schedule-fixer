<?php
/**
 * Plugin Name: 1815 - Missed Schedule Fixer
 * Plugin URI: https://www.1815.nl
 * Description: Fixes "missed schedule" problems with posts
 * Version: 1.0.0
 * Author: 1815
 * Author URI: https://www.1815.nl
 *
 * @package AchttienVijftien\Plugin\MissedScheduleFixer
 **/

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

\AchttienVijftien\Plugin\MissedScheduleFixer\Bootstrap::get_instance()->init();
