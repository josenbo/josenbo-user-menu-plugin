<?php
/**
* Plugin Name:    Josenbo User Menu
* Plugin URI:     https://github.com/josenbo/josenbo-user-menu-plugin
* Description:    Provides menu widgets for user access and user profile actions. This is a customized version of WPBrigade's Login Logout Menue plugin (see https://wpbrigade.com/wordpress/plugins/loginpress/)
* Version:        1.0.0
* Author:         Jochen Stein
* Author URI:     https://github.com/josenbo/
* Text Domain:    josenbo-user-menu
* Domain Path:    /languages
*
* @package josenboxum
* @category Core
* @author josenbo
**/

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
        '#josenboxum-login#'        => __( 'Log In',   'josenbo-user-menu' ),
        '#josenboxum-logout#'       => __( 'Log Out',  'josenbo-user-menu' ),
        '#josenboxum-loginlogout#'  => __( 'Log In',   'josenbo-user-menu' ) . ' | ' . __( 'Log Out', 'josenbo-user-menu' ),
        '#josenboxum-register#'     => __( 'Register', 'josenbo-user-menu' ),
        '#josenboxum-profile#'      => __( 'Profile',  'josenbo-user-menu' )
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
            <a href="javascript:void(0);" class="help" onclick="jQuery( '#josenbo-user-menu-help' ).toggle();"><?php _e( 'Help', 'josenbo-user-menu' ); ?></a>
            <span class="hide-if-js" id="josenbo-user-menu-help"><br /><a name="josenbo-user-menu-help"></a>
              <?php
              echo '&#9725;' . esc_html__( 'To redirect user after login/logout/register just add a relative link after the link\'s keyword, example :', 'josenbo-user-menu' ) . ' <br /><code>#josenboxum-loginlogout#index.php</code>.';
              echo '<br /><br />&#9725;' . esc_html__( 'You can also use', 'josenbo-user-menu' ) . ' <code>%current-page%</code> ' . esc_html__( 'to redirect the user on the current visited page after login/logout/register, example :', 'josenbo-user-menu' ) . ' <code>#josenboxum-loginlogout#%current-page%</code>.<br /><br />';
              echo sprintf( __( 'To get plugin support contact us on <a href="%1$s" target="_blank">plugin support forum</a> or <a href="%2$s" target="_blank">contact us page</a>.', 'josenbo-user-menu'), 'https://wpbrigade.com/wordpress/plugins/josenbo-user-menu/', 'https://wpbrigade.com/contact/' ) . '<br /><br />';
                ?>
              </span>
            </span>

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
          return esc_html( isset( $titles[0] ) ? $titles[0] : $title );
        } else {
          return esc_html( isset( $titles[1] ) ? $titles[1] : $title );
        }
      }

      function josenbo_user_setup_menu( $item ) {

        global $pagenow;

        if ( $pagenow != 'nav-menus.php' && ! defined( 'DOING_AJAX' ) && isset( $item->url ) && strstr( $item->url, '#josenboxum' ) != '' ) {

          $item_url = substr( $item->url, 0, strpos( $item->url, '#', 1 ) ) . '#';
          $item_redirect = str_replace( $item_url, '', $item->url );

          if ( $item_redirect == '%current-page%' ) {
            $item_redirect = $_SERVER['REQUEST_URI'];
          }

          switch ( $item_url ) {
            case '#josenboxum-loginlogout#' :

            $item_redirect = explode( '|', $item_redirect );

            if ( count( $item_redirect ) != 2 ) {
              $item_redirect[1] = $item_redirect[0];
            }

            if ( is_user_logged_in() ) {

              $item->url = wp_logout_url( $item_redirect[1] );
            } else {

              $item->url = wp_login_url( $item_redirect[0] );
            }

            $item->title = $this->josenbo_user_setup_title( $item->title ) ;
            break;

            case '#josenboxum-login#' :

            if ( is_user_logged_in() ) {
              return $item;
            }

            $item->url = wp_login_url( $item_redirect );
            break;

            case '#josenboxum-logout#' :
            if ( ! is_user_logged_in() ) {
              return $item;
            }

            $item->url = wp_logout_url( $item_redirect );
            break;

            case '#josenboxum-register#' :

            if ( is_user_logged_in() ) {
              return $item;
            }

            $item->url = wp_registration_url();
            break;

            case '#josenboxum-profile#' :
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
          if ( strstr( $item->url, '#josenboxum' ) != '' ) {
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
