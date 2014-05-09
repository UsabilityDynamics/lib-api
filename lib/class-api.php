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
     * API Namespace
     *
     * @public
     * @static
     * @property $namespace
     * @type {String}
     */
    public static $namespace = null;

    /**
     * Routes
     *
     * @public
     * @static
     * @property $routes
     * @type {Object}
     */
    public static $_routes = Array();

    /**
     * Our constructor, should setup some default routes
     *
     * @returns API $this
     */
    function __construct(){
      /** Let's go ahead and add our routes */
      add_action( 'plugins_loaded', array( $this, 'create_routes' ) );
      /** Return this */
      return $this;
    }

    /**
     * Creates some of our global routes
     */
    public function create_routes(){
      /** Ok, add our default routes now */
      self::define( '/v1/routes', array( $this, 'listRoutes' ) );
    }

    /**
     * Define API Endpoints.
     *
     *    Veneer\API::define( '/merchant-feed/google', array( 'CDO\Application\API\MerchantFeed', 'compute' ) )
     *
     * @param       $path
     * @param null  $handler
     * @param array $args
     *
     * @return array|void
     */
    public static function define( $path, $handler = null, $args = array() ) {

      if( $handler && is_array( $handler ) && !$args ) {
        $_args = $handler;
      } else {
        $_args = $args;
      }

      $_args = Utility::parse_args( $_args, array(
        'path' => $path,
        'method' => 'GET',
        'handler' => $handler,
        'namespace' => static::$namespace,
        'scopes' => array(),
        'parameters' => array()
      ));

      if( !is_callable( $_args->handler ) ) {
        return _doing_it_wrong( 'UsabilityDynamics\Veneer\API::define', 'Handler not callable.', null );
      }

      /** Determine our handler */
      $handler = $_args->handler;
      if( is_array( $handler ) && count( $handler ) == 2 ){
        if( is_string( $handler[ 0 ] ) && is_string( $handler[ 2 ] ) ){
          $handler = join( '::', $handler );
        }
      }

      $_route = array(
        '_type' => 'route',
        'path' => self::get_path( $path, $_args ),
        'namespace' => $_args->namespace,
        'method' => $_args->method,
        'url' => self::get_url( $path, $_args ),
        'parameters' => $_args->parameters,
        'scopes' => $_args->scopes,
        'detail' => array(
          'handler' => $handler,
          'url' => add_query_arg( array( 'action' => self::get_path( $path, $_args ) ), admin_url( 'admin-ajax.php' ) ),
          'action' => current_action()
        )
      );

      add_action( 'wp_ajax_' . self::get_path( $path, $_args ), $_args->handler );
      add_action( 'wp_ajax_nopriv_' . self::get_path( $path, $_args ), $_args->handler );

      self::$_routes[] = $_route;

      return $_route;

    }

    /**
     * List Routes
     *
     * @param array $args
     *
     * @return array
     */
    public function routes( $args = array() ) {

      // Filter.
      $args = (object) Utility::extend( array(), $args);

      return API::$_routes;

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

    /**
     * List Defined API Routes
     */
    static public function listRoutes() {
      if( !current_user_can( 'read' ) ) {
        return;
      }
      wp_send_json(array(
        'ok' => true,
        'routes' => API::routes()
      ));
    }

    /**
     * Utility function
     */
    public static function get_path( $path, $args ) {
      return apply_filters( 'usabilitydynamics::api::get_path', str_replace( '//', '/', ( '/' . ( $args->namespace ? $args->namespace . '/' : '' ) . $path ), $args ) );
    }
    /**
     * Utility function
     */
    public static function get_url( $path, $args ) {
      return apply_filters( 'usabilitydynamics::api::get_url', add_query_arg( array(
        'action' => self::get_path( $path, $args )
      ), admin_url( 'admin-ajax.php' ) ), $args );
    }

  }

}
