<?php

/**
 *
 * @link       https://humbertosilva.com/
 * @since      1.0.0
 *
 * @package    Admin_Live_Search
 * @subpackage Admin_Live_Search/admin
 */

/**
 *
 * @package    Admin_Live_Search
 * @subpackage Admin_Live_Search/admin
 * @author     Humberto Silva <humberto@humbertosilva.com>
 */
class Admin_Live_Search_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $screen;
	private $screen_id;
	private $cpt_status;
	private $is_list_page;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', 'admin_live_search_menu' );

		function admin_live_search_menu() {
			add_options_page(
				'Options',
				'Admin Live Search',
				'manage_options',
				'admin-live-search.php',
				'Admin_Live_Search_Admin::admin_live_search_help'
			);
		}

		add_filter(
				'plugin_action_links_' . ADMIN_LIVE_SEARCH_BASE,
				'Admin_Live_Search_Admin::admin_live_search_links'
		);

		$this->is_list_page = false;
		add_action( 'current_screen', array( $this, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
		add_action( 'admin_head', array( $this, 'add_screen_js' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		$this->cpt_status = (get_option('admin_live_search_cpt_status',false) == 1);
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin-live-search-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin-live-search-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_screen_js(){
		echo '<script type="text/javascript">admin_live_search_cpt = ';
		echo ($this->cpt_status == true) ? 'true' : 'false';
		echo ';admin_live_search_screen_id = "'.$this->screen_id.'";';

		$show_cpt_notice = false;
		if (isset($_REQUEST['post_type'])) {
			if ( $_REQUEST['post_type'] != 'page' && $_REQUEST['post_type'] != 'post' ) $show_cpt_notice = true;
		}
		if ( $this->is_list_page && $show_cpt_notice && get_option('admin_live_search_cpt_status') != 1 ) {
		echo 'admin_live_search_show = false;';
		} else {
			if ( $this->is_list_page) echo 'admin_live_search_show = true;';
		}
		echo '</script>';
		?>
		<select id="admin_live_search_filter" style="display:none">
			<option value="0"><?php echo __('Title and Text','admin-live-search'); ?></option>
			<option value="1"><?php echo __('Title Only','admin-live-search'); ?></option>
			<option value="2"><?php echo __('Text Only','admin-live-search'); ?></option>
		</select>
		<?php
	}

	public function setup_screen() {
		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) { // WPCS: input var ok.
			$screen_id = als_clean( wp_unslash( $_REQUEST['screen'] ) ); // WPCS: input var ok, sanitization ok.
		}

		$this->screen_id = $screen_id;
		$this->screen = $this->screen;

		$this->is_list_page = false;
		if ( $this->screen_id == 'edit-post') $this->is_list_page = true;
		if ( $this->screen_id == 'edit-page') $this->is_list_page = true;
		if ( substr($this->screen_id,0,5) == 'edit-' && isset($_REQUEST['post_type'])) $this->is_list_page = true;

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	public function admin_live_search() {
			$_url = esc_url($_REQUEST['url']);
			$_base = get_site_url();
			// extra security check
			if ( $_base != substr($_url,0,strlen($_base)) ) {
				wp_die();
			}

			$_admin_live_search_filter = intval( $_REQUEST['admin_live_search_filter']);

			$_keyword = sanitize_text_field($_REQUEST['keyword']);

			$_params = sanitize_text_field($_REQUEST['fields']);
			$_url_get = $_GET;

			parse_str($_params, $_REQUEST);
			parse_str($_params, $_GET);
			parse_str($_params, $params);

			// fix because of punctuation and special chars
			$_REQUEST['s'] = $_keyword;

			global $post_type, $post_type_object;
			ob_start();

			if ( isset( $_REQUEST['post_type'] ) && post_type_exists( $_REQUEST['post_type'] ) )
				$typenow = $_REQUEST['post_type'];
			else
				$typenow = '';

			if ( isset( $_REQUEST['taxonomy'] ) && taxonomy_exists( $_REQUEST['taxonomy'] ) )
				$taxnow = $_REQUEST['taxonomy'];
			else
				$taxnow = '';

			$pagenow = 'edit.php';
			$GLOBALS['hook_suffix'] = $typenow;

			do_action( 'admin_init' );
			do_action( 'setup_screen' );
			$_screen = set_current_screen();
			do_action( "load-{$pagenow}" );

			$post_type = $typenow;
			$post_type_object = get_post_type_object( $post_type );
			if ( ! $post_type_object )
				wp_die( __( 'Invalid post type.' ) );
			if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
				wp_die(
					'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
					'<p>' . __( 'Sorry, you are not allowed to edit posts in this post type.' ) . '</p>',
					403
				);
			}

// 			$wp_list_table = _get_list_table('WP_Posts_List_Table', array( 'screen' => $this->screen_id ));

			if ( isset( $this->screen_id ) )
				$this->screen_id = convert_to_screen( $this->screen_id );
			elseif ( isset( $GLOBALS['hook_suffix'] ) )
				$this->screen_id = get_current_screen();
			else
				$this->screen_id = null;

			$wp_list_table = new Admin_Live_Search_Posts_List_Table( array( 'screen' => $this->screen_id , 'admin_live_search_filter' => $_admin_live_search_filter , 'request' => $_url_get) );

			$pagenum = $wp_list_table->get_pagenum();

			if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
				printf( ' <span class="subtitle admin_live_search_rt">' . __( 'Search results for &#8220;%s&#8221;' ) . ' <em>(via <a href="'. add_query_arg(['page' => 'admin-live-search.php',],admin_url('options-general.php') ).'">'.  'Admin Live Search'  . '</a>)<em>' . '</span>', sanitize_text_field($_REQUEST['s']) );
			}
			$wp_list_table->prepare_items();
			$wp_list_table->views();
			$wp_list_table->display();
			if ( $wp_list_table->has_items() )
				$wp_list_table->inline_edit();
			?>
			<div id="ajax-response"></div>
			<br class="clear" />
			<?php
			$html = ob_get_clean();
			wp_send_json_success( $html );
			wp_die();
	}

	public static function admin_live_search_help() {
		?>
		<h1>Admin Live Search</h1>
		<br>
		<h2><?php echo __( 'How to use the Admin Live Search plugin:', 'admin-live-search' ); ?></h2>
		<p>
			<?php echo __( 'This plugin provides live search for the dashboard/admin area only.', 'admin-live-search' ).'<br>'; ?>
			<?php echo __( 'Just type and the results will appear as you type without refreshing the page.', 'admin-live-search' ).'<br>'; ?>
			<?php echo __( 'Sorting and pagination are also live without refreshing the page.', 'admin-live-search' ).'<br>'; ?>
			<br>
			<?php echo __( "The live search doesn't work properly with custom post types (like products, projects, etc) because custom columns are not yet supported so the results appear without the extra columns added by custom post types.", 'admin-live-search' ).'<br>'; ?>
			<?php echo __( "You can enable or disable the Admin Live Search for custom post types below:", 'admin-live-search' ).'<br>'; ?>
				<p style="line-height:24px">
			<?php echo '<span class="spinner admin_live_search_sp"></span><input type="checkbox" value="1" id="admin_live_search_cpt_status_chkbox" ';
			if (get_option('admin_live_search_cpt_status',false) == 1) echo ' checked="checked"';
			echo ' /> ' . __( 'Enable Admin Live Search for Custom Post Types', 'admin-live-search' ).'<br>'; ?>
				</p>
				<br>
		</p>

		<h2><?php echo __( 'Privacy and Cookies:', 'admin-live-search' ); ?></h2>
		<p>
			<?php echo __( 'This plugin does NOT collect any personal information.', 'admin-live-search' ).'<br>'; ?>
			<?php echo __( "All information inserted by the admin user like the search keyword is used only locally in your website's admin area.", 'admin-live-search' ).'<br>'; ?>
			<?php echo __( 'If you have any question regarding privacy or information used by this plugin please', 'admin-live-search' ) . ' ' . ' <a target="_blank" href="https://wordpress.org/support/plugin/admin-live-search#postform">' . esc_attr__( 'Contact Support', 'admin-live-search' ) . '</a>'   . '<br>'; ?>
			<br>
		</p>

		<h2><?php echo __( 'Further support:', 'admin-live-search' ); ?></h2>
		<p>
			<?php echo __( 'If you found a problem or want a new feature please', 'admin-live-search' ) . ' <a target="_blank" href="https://wordpress.org/support/plugin/admin-live-search#postform">' . esc_attr__( 'Contact Support', 'admin-live-search' ) . '</a>' .'<br>'; ?>
			<br>
		</p>

		<h2><?php echo __( 'Credits:', 'admin-live-search' ); ?></h2>
		<p>
			<?php echo __( 'Images/icons from openclipart.org: ', 'admin-live-search' ) ?>
			<?php echo '( <a href="https://openclipart.org/user-detail/libberry" target="_blank">libberry</a> ) '; ?>
			<?php echo '( <a href="https://openclipart.org/user-detail/graingert" target="_blank">graingert</a> )'; ?>
			<br>
			<br>
		</p>

		<h2><?php echo __( 'Support the development of this plugin:', 'admin-live-search' ); ?></h2>
		<p>
			<?php echo __( 'This plugin is FREE to use.', 'admin-live-search' ).'<br>'; ?>
			<?php echo __( 'If you find this plugin usefull please consider that it takes the time and efford to develop and maintain this plugin and help the author.', 'admin-live-search' ).'<br>'; ?>
			<?php echo '1. ' . str_replace( array( '[icon]', '[wporg]' ), array( '<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/admin-live-search#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>', '<a target="_blank" href="http://wordpress.org/plugins/admin-live-search/" >wordpress.org</a>' ),__( 'Add your [icon] on [wporg] to spread the love.', 'admin-live-search' ) ) .'<br>'; ?>
			<?php echo '2. '. '<a href="https://www.paypal.me/humbertosilvacom" target="_blank">'. __('Donate') .'</a><br>'; ?>
			<br>
			<?php echo __( 'Thank you.', 'admin-live-search' ).'<br>'; ?>
		</p>
		<hr>

	<?php
	}

	public static function admin_live_search_links( $data ) {
		return array_merge($data,[sprintf('<a href="%s">%s</a>',add_query_arg(['page' => 'admin-live-search.php',],admin_url('options-general.php') ), __("Settings")),]);
	}

	public function admin_live_search_set_cpt_status() {
		if ( $_GET['admin_live_search_cpt_status_chkbox'] == 1 ) {
			update_option('admin_live_search_cpt_status', 1);
		} else {
			update_option('admin_live_search_cpt_status', 0);
		}
		wp_die();
	}

	public function admin_notices(){
		$show_cpt_notice = false;
		if (isset($_REQUEST['post_type'])) {
			if ( $_REQUEST['post_type'] != 'page' && $_REQUEST['post_type'] != 'post' ) $show_cpt_notice = true;
		}
		if ( $this->is_list_page && $show_cpt_notice && get_option('admin_live_search_cpt_status') != 1 ) { ?>
			<div class="notice notice-warning is-dismissible">
					<p>Admin Live Search <?php _e( 'is disabled for this content.', 'admin-live-search' ); echo '  <em><a href="'. add_query_arg(['page' => 'admin-live-search.php',],admin_url('options-general.php') ).'">'.  __( 'Why?', 'admin-live-search' ) . '</a></em>'; ?></a></p>
			</div>
			<?php }
	}


		public function register_query_vars( $vars ) {
			$vars[] = 'admin_live_search_filter';
			return $vars;
		}



}

function als_clean( $var ) {	if ( is_array( $var ) ) { return array_map( 'als_clean', $var ); } else {		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;	} }
