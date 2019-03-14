<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link       https://bitbucket.org/sarkonet/wordpress-plugin-login-shortcode/
 * @since      1.0.0
 * @package    jsnusermenu
 * @author     josenbo
 *
 * @wordpress-plugin
 * Plugin Name:    Josenbo User Menu
 * Plugin URI:     https://github.com/josenbo/josenbo-user-menu-plugin
 * Description:    Provides menu widgets for user access and user profile actions. This is a customized version of WPBrigade's Login Logout Menue plugin (see https://wpbrigade.com/wordpress/plugins/loginpress/)
 * Version:        1.0.0
 * Author:         Jochen Stein
 * Author URI:     https://github.com/josenbo/
 * Text Domain:    josenbo-user-menu
 * Domain Path:    /languages
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

if ( !class_exists( 'Josenbo_User_Menu' ) ) :

  /**
  *
  */
  class Josenbo_User_Menu {

    /**
    * @var string
    * @since 1.0.0
    */
    public $version = '1.0.0';

    /**
    * @var The single instance of the class
    * @since 1.0.0
    */
    protected static $_instance = null;

    /** * * * * * * * *
    * Class constructor
    * @since 1.0.0
    * * * * * * * * * */
    public function __construct() {

      $this->define_constants();
      $this->_hooks();
    }

    /**
    * Define Josenbo User Menu Constants
    * @since 1.0.0
    */
    private function define_constants() {

      $this->define( 'JOSENBO_USER_MENU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
      $this->define( 'JOSENBO_USER_MENU_DIR_PATH',        plugin_dir_path( __FILE__ ) );
      $this->define( 'JOSENBO_USER_MENU_DIR_URL',         plugin_dir_url( __FILE__ ) );
      $this->define( 'JOSENBO_USER_MENU_ROOT_PATH',       dirname( __FILE__ ) . '/' );
      $this->define( 'JOSENBO_USER_MENU_VERSION',         $this->version );
      $this->define( 'JOSENBO_USER_MENU_FEEDBACK_SERVER', 'https://jochen-stein.com/' );
    }

    /**
    * Hook into actions and filters
    * @since  1.0.0
    */
    private function _hooks() {

      add_action( 'plugins_loaded', array( $this, 'textdomain' ) );
      add_action( 'admin_head-nav-menus.php', array( $this, 'admin_nav_menu' ) );
      add_filter( 'wp_setup_nav_menu_item', array( $this, 'josenbo_user_setup_menu' ) );
      add_filter( 'wp_nav_menu_objects', array( $this, 'josenbo_user_menu_objects' ) );
    }

    /**
    * Main Instance
    *
    * @since 1.0.0
    * @static
    * @see josenbo_user_menu_loader()
    * @return Main instance
    */
    public static function instance() {
      if ( is_null( self::$_instance ) ) {
        self::$_instance = new self();
      }
      return self::$_instance;
    }


    /**
    * Load Languages
    * @since 1.0.0
    */
    public function textdomain() {

      $plugin_dir =  dirname( plugin_basename( __FILE__ ) ) ;
      load_plugin_textdomain( 'josenbo-user-menu', false, $plugin_dir . '/languages/' );
    }

    /* Registers Login/Logout/Register Links Metabox */
    function admin_nav_menu() {
      add_meta_box( 'josenbo_user_menu', __( 'Josenbo User Menu', 'josenbo-user-menu' ), array( $this, 'admin_nav_menu_callback' ), 'nav-menus', 'side', 'default' );
    }

    /* Displays Login/Logout/Register Links Metabox */
    function admin_nav_menu_callback(){

      global $nav_menu_selected_id;

      $elems = array(
        '#jsnusermenu-login#'        => __( 'Log In',   'josenbo-user-menu' ),
        '#jsnusermenu-logout#'       => __( 'Log Out',  'josenbo-user-menu' ),
        '#jsnusermenu-loginlogout#'  => __( 'Log In',   'josenbo-user-menu' ) . ' | ' . __( 'Log Out', 'josenbo-user-menu' ),
        '#jsnusermenu-register#'     => __( 'Register', 'josenbo-user-menu' ),
        '#jsnusermenu-profile#'      => __( 'Profile',  'josenbo-user-menu' )
      );
      $logitems = array(
        'db_id' => 0,
        'object' => 'bawlog',
        'object_id',
        'menu_item_parent' => 0,
        'type' => 'custom',
        'title',
        'url',
        'target' => '',
        'attr_title' => '',
        'classes' => array(),
        'xfn' => '',
      );

      $elems_obj = array();
      foreach ( $elems as $value => $title ) {
        $elems_obj[ $title ]            = (object) $logitems;
        $elems_obj[ $title ]->object_id = esc_attr( $value );
        $elems_obj[ $title ]->title     = esc_attr( $title );
        $elems_obj[ $title ]->url       = esc_attr( $value );
      }

      $walker = new Walker_Nav_Menu_Checklist( array() );
      ?>
      <div id="josenbo-umlinks" class="josenboumlinksdiv">

        <div id="tabs-panel-josenbo-umlinks-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
          <ul id="josenbo-umlinkschecklist" class="list:josenbo-umlinks categorychecklist form-no-clear">
            <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $elems_obj ), 0, (object) array( 'walker' => $walker ) ); ?>
          </ul>
        </div>

        <p class="button-controls">
          <span class="list-controls hide-if-no-js">
            <span class="add-to-menu">
              <input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'josenbo-user-menu' ); ?>" name="add-josenbo-umlinks-menu-item" id="submit-josenbo-umlinks" />
              <span class="spinner"></span>
            </span>
          </p>

        </div>
        <?php

      }

      function josenbo_user_setup_title( $title ) {

        $titles = explode( '|', $title );

        if ( ! is_user_logged_in() ) {
          return '<i class="far fa-user-circle"></i>';
        } else {
          return '<i class="fas fa-user-circle"></i>';
        }
      }

      function josenbo_user_setup_menu( $item ) {

        global $pagenow;

        if ( $pagenow != 'nav-menus.php' && ! defined( 'DOING_AJAX' ) && isset( $item->url ) && strstr( $item->url, '#jsnusermenu' ) != '' ) {

          $item_url = substr( $item->url, 0, strpos( $item->url, '#', 1 ) ) . '#';

          switch ( $item_url ) {
            case '#jsnusermenu-loginlogout#' :

              if ( is_user_logged_in() ) {

                $item->url = wp_logout_url( get_home_url() );
              } else {
                $item->url = wp_login_url( $_SERVER['REQUEST_URI'] );
              }

              $item->title = $this->josenbo_user_setup_title( $item->title ) ;
              break;

            case '#jsnusermenu-login#' :

              if ( is_user_logged_in() ) {
                return $item;
              }

              $item->url = wp_login_url( $_SERVER['REQUEST_URI'] );
              break;

            case '#jsnusermenu-logout#' :
              if ( ! is_user_logged_in() ) {
                return $item;
              }
              
              $item->url = wp_logout_url( get_home_url() );
              break;

            case '#jsnusermenu-register#' :

              if ( is_user_logged_in() ) {
                return $item;
              }

              $item->url = wp_registration_url();
              break;

            case '#jsnusermenu-profile#' :
              if ( ! is_user_logged_in() ) {
                return $item;
              }

              if ( function_exists('bp_core_get_user_domain') ) {
                $url = bp_core_get_user_domain( get_current_user_id() );
              } else if ( function_exists('bbp_get_user_profile_url') ) {
                $url = bbp_get_user_profile_url( get_current_user_id() );
              } else if ( class_exists( 'WooCommerce' ) ) {
                $url = get_permalink( get_option('woocommerce_myaccount_page_id') );
              } else {
                $url = get_edit_user_link();
              }

              $item->url = esc_url( $url );
              break;
          }
          $item->url = esc_url( $item->url );
        }
        return $item;
      }


      function josenbo_user_menu_objects( $sorted_menu_items ) {

        foreach ( $sorted_menu_items as $menu => $item ) {
          if ( strstr( $item->url, '#jsnusermenu' ) != '' ) {
            unset( $sorted_menu_items[ $menu ] );
          }
        }
        return $sorted_menu_items;
      }


      /**
      * Define constant if not already set
      * @param  string $name
      * @param  string|bool $value
      */
      private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
          define( $name, $value );
        }
      }

    }

  endif;


  /**
  * Returns the main instance of WP to prevent the need to use globals.
  *
  * @since  1.0.0
  * @return Josenbo_User_Menu
  */
  function josenbo_user_menu_loader() {
    return Josenbo_User_Menu::instance();
  }

  // Call the function
  josenbo_user_menu_loader();
