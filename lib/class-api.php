<?php
/**
 * API Library
 *
 * @author team@UD
 * @version 0.1.1
 * @module UsabilityDynamics
 */
namespace UsabilityDynamics {

  /**
   * API Library
   *
   * @module UsabilityDynamics
   * @author team@UD
   *
   * @version 0.1.2
   *
   * @class API
   * @extends Utility
   */
  class API {

    /**
     * API Class version.
     *
     * @public
     * @static
     * @property $version
     * @type {Object}
     */
    public static $version = '0.1.2';

    /**
     * Define API Endpoints.
     *
     *    Veneer\API::define( '/merchant-feed/google', array( 'CDO\Application\API\MerchantFeed', 'compute' ) )
     *
     * @param      $path
     * @param null $handler
     *
     */
    public static function define( $path, $handler = null ) {

      if( !is_callable( $handler ) ) {
        add_action( 'wp_ajax_' . $path, array( 'UsabilityDynamics\Veneer\API', 'default_handler' ) );
        return _doing_it_wrong( 'UsabilityDynamics\Veneer\API::define', 'Handler not callable.', null );
      }

      add_action( 'wp_ajax_' . $path, $handler );
      add_action( 'wp_ajax_nopriv_' . $path, $handler );

    }

    /**
     * Default Response Handler.
     *
     */
    public static function default_handler() {
      self::send( new \WP_Error( "API endpoint does not have a handler." ) );
    }

    /**
     * Send Response
     *
     * @todo Add content-type detection for XML response handling.
     *
     * @param       $data
     * @param array $headers
     *
     * @return bool
     */
    public static function send( $data, $headers = array() ) {

      nocache_headers();

      if( is_string( $data ) ) {
        return die( $data );
      }

      // Error Response.
      if( is_wp_error( $data ) ) {
        return wp_send_json(array(
          "ok" => false,
          "error" => $data
        ));
      }

      // Standard Object Response.
      if( ( is_object( $data ) || is_array( $data ) ) && !is_wp_error( $data ) ) {

        $data = (object) $data;

        if( !isset( $data->ok ) ) {
          $data = (object) ( array( 'ok' => true ) + (array) $data );
        }

        return wp_send_json( $data );
      }

    }

  }

}
