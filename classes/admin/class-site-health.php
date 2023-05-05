<?php

/**
 * @Author: Timi Wahalahti
 * @Date:   2018-04-25 17:08:45
 * @Last Modified by:   Timi Wahalahti
 * @Last Modified time: 2023-05-05 11:50:43
 */

namespace CRMServiceWP\Admin\SiteHealth;

use CRMServiceWP;
use CRMServiceWP\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 *  Class for plugin settings.
 *
 *  @since 1.0.0
 */
class SiteHealth extends CRMServiceWP\Plugin {
	/**
	 *  Instance of helper.
	 *
	 *  @var resource
	 */
	protected static $helper;

	/**
	 *  Fire it up!
	 *
	 *  @since 1.0.0
	 */
	public function __construct() {
		// Get instance of helper.
		self::$helper = new CRMServiceWP\Helper\Helper();

		// Ignition done, give some kick.
		self::run();
	} // end __construct

	/**
	 *  Add hooks.
	 *
	 *  @since  1.0.0
	 */
	protected function run() {
		// Tests
    add_filter( 'site_status_tests',            array( __CLASS__, 'status_tests' ) );
    add_filter( 'site_status_test_php_modules', array( __CLASS__, 'site_status_test_php_modules' ) ); // require soap
	} // end run

  public static function status_tests( $tests ) {
    $tests['direct']['crmservice_api_health'] = array(
      'label' => \wp_kses( 'CRM-service API connection.', 'crmservice' ),
      'test'  => array( __CLASS__, 'test_crmservice_api_health' ),
    );

    return $tests;
  } // end status_tests

  public static function site_status_test_php_modules( $modules ) {
    $modules['soap'] = array(
      'extension' => 'soap',
      'required'  => true,
    );

    return $modules;
  } // end site_status_test_php_modules

  public static function test_crmservice_api_health() {
    $api_credentials = self::$helper->check_api_settings_existance();
    $api_credentials_health = self::$helper->check_api_credentials_health();

    $result = array(
      'label'       => \wp_kses( 'API connection is healthy', 'crmservice' ),
      'status'      => 'good',
      'badge'       => array(
        'label' => 'CRM-service',
        'color' => 'blue',
      ),
      'description' => \wp_kses( 'API base url and key have been set, and site can communicate with CRM-service.', 'crmservice' ),
      'actions'     => '',
      'test'        => 'crmservice_api_health',
    );

    if ( ! $api_credentials ) {
      $result['status']      = 'critical';
      $result['label']       = \wp_kses( 'API base url and key not set', 'crmservice' );
      $result['description'] = \wp_sprintf( wp_kses( 'Setting up <a href="%s">API credentials</a> is needed. If you don\'t know what those are, please contact our support.', 'crmservice' ), self::$helper->get_plugin_page_url( array( 'page' => 'crmservice' ) ) );
    } elseif ( ! $api_credentials_health ) {
      $result['status']      = 'critical';
      $result['label']       = \wp_kses( 'Can\'t connect to CRM-service', 'crmservice' );
      $result['description'] = \wp_sprintf( wp_kses( 'You might have added wrong <a href="%s">API credentials</a>. Please check your API credntials. With correct API credentials, there might be a temporary problem with our API. If this problem persist, please <a href="%s">contact our support</a>.', 'crmservice' ), self::$helper->get_plugin_page_url( array( 'page' => 'crmservice' ) ), self::$helper->get_plugin_page_url( array( 'page' => 'crmservice', 'tab' => 'bugreport' ) ) );
    }

    return $result;
  } // end test_crmservice_api_health
}

new SiteHealth();
