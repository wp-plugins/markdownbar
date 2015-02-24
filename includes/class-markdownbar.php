<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MarkdownBar {

	/**
	 * The single instance of MarkdownBar.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'markdownbar';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

        add_filter('quicktags_settings', array( $this, 'remove_quicktag_buttons'));
        add_action( 'admin_print_footer_scripts', array( $this, 'markdownbar_buttons' ) );

	} // End __construct ()

    function markdownbar_buttons() {
        if (wp_script_is('quicktags')){
            ?>
                <script type="text/javascript">
                QTags.addButton( 'mdb_b', 'b', '**', '**', 'b', 'Bold', 1 );
                QTags.addButton( 'mdb_i', 'i', '*', '*', 'i', 'Italic', 2 );
                QTags.addButton( 'mdb_quote', '&rdquo;', '> ', '', 'q', 'Blockquote', 3 );            
                QTags.addButton( 'mdb_link', 'link', link_prompt_js, '', '', 'Link', 4 );
                QTags.addButton( 'mdb_code', 'code', '```\n', '\n```', 'c', 'Code', 5 );
                QTags.addButton( 'mdb_table', 'table', 'First Header  | Second Header\n------------- | -------------\nContent Cell  | Content Cell', '', 't', 'Table', 6 );
                QTags.addButton( 'mdb_h1', 'h1', '# ', ' #', '1', 'H1', 7 );
                QTags.addButton( 'mdb_h2', 'h2', '## ', ' ##', '2', 'H2', 8 );
                QTags.addButton( 'mdb_h3', 'h3', '### ', ' ###', '3', 'H3', 9 );
                QTags.addButton( 'mdb_h4', 'h4', '#### ', ' ####', '4', 'H4', 10 );
                QTags.addButton( 'mdb_help', '?', help_popup_js, '', '', 'Help', 11);
                <?php 
                $this->link_prompt();
                $this->help_popup();
                 ?>
                </script>
            <?php
        }
    }
    function remove_quicktag_buttons( $qtInit ) {
        $qtInit['buttons'] = 'fullscreen';
        return $qtInit;
    }

    function help_popup() {
        ?>
        function help_popup_js() {
            var win = window.open('https://help.github.com/articles/markdown-basics/', '_blank');
            win.focus();
        }
        <?php
    }

    function link_prompt() {
        ?>
        function link_prompt_js(e, c, ed) {
            var prmt, t = this;
            if ( ed.canvas.selectionStart !== ed.canvas.selectionEnd ) {
                // if we have a selection in the editor define out tagStart and tagEnd to wrap around the text
                // prompt the user for the abbreviation and return gracefully on a null input
                prmt = prompt('Enter Link Destination');
                if ( prmt === null ) return;
                t.tagStart = '[';
                t.tagEnd = '](' + prmt + ')';            
            } else {
                // last resort, no selection and no open tags
                // so prompt for input and just open the tag
                prmt = prompt('Enter Link Name');
                dest = prompt('Enter Link Destination');
                if ( prmt === null ) return;
                t.tagStart = '[' + prmt + '](' + dest + ')';
                t.tagEnd = false;
                
            }
            // now we've defined all the tagStart, tagEnd and openTags we process it all to the active window
            QTags.TagButton.prototype.callback.call(t, e, c, ed);
        };
        <?php
    }

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Main MarkdownBar Instance
	 *
	 * Ensures only one instance of MarkdownBar is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see MarkdownBar()
	 * @return Main MarkdownBar instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
