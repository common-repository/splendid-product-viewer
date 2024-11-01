<?php
class pv_backend {
	

	private $pv_plugin_dir_url 	= '';
	private $plugin_name		= 'Product Viewer';
	private $post_type 			= 'pv';
	private $uploaders 			= 	array(
										array(	'name' 			=> 	'pv_image_1',
												'label'			=>	'Product Image',
												'fallback_url' 	=> 	'',
												'isThumbImg'	=> 	false
												),
										array(	'name' 			=> 	'pv_image_2',
												'label'			=>	'Product Thumbnail',
												'fallback_url' 	=> 	'',
												'isThumbImg'	=> 	true
												)
									);
	
	private $pv_upload_path_sys;
	private $pv_upload_path_url;
	
	private $no;
	
	public function __construct($pluginFilePath, $arrupload_pathDir) {
		
		$this->pv_plugin_dir_url = $pluginFilePath;
		
		$this->pv_upload_path_sys = $arrupload_pathDir['basedir'] . '/splendid-product-viewer/';
		$this->pv_upload_path_url = $arrupload_pathDir['baseurl'] . '/splendid-product-viewer/';
							
		$this->pv_add_backend_actions_filters();
		
	}
	
	
	private function pv_add_backend_actions_filters () {
		
		// ACTIONS
		
		// start custom upload for pv
		add_action('save_post', array($this, 'pv_handle_upload_file'), 10, 1);
		// start the product viewer code
		add_action('admin_init', array($this, 'init_admin'));
		// add column with thumbnails to the admin post overview
		add_action("manage_posts_custom_column", array($this, 'admin_column_content'));
		// add column with thumbnails to the admin post overview
		add_action("manage_page_posts_custom_column", array($this, 'admin_column_content'));
		// add formtag to upload multiple images
		add_action('post_edit_form_tag', array($this, 'add_post_enctype'));
		// add scripts
		add_action( 'admin_enqueue_scripts', array($this,'pv_admin_script') ); 
		// add styles
		add_action( 'admin_enqueue_scripts', array($this, 'pv_admin_style') ); 
		// add custom post type product viewer
		add_action('init', array($this,'pv_register_productviewer'));
		// add lang file
		add_action('plugins_loaded',  array($this,'loadLangFile'));
		// plugin icons
		add_action( 'admin_head', array($this,'wpt_portfolio_icons'));
		// when editing post update
		add_action("manage_" . $this->post_type . "_posts_custom_column", array($this, 'admin_column_content'));
		// custome taxonomy so category only apperas in pv
		add_action( 'init', array($this,'build_taxonomies'));  

		
		// FILTERS
		
		// add filters
		add_filter("manage_posts_custom_column", array($this, 'admin_columns'));
		// when editing post update
		add_filter("manage_edit-" . $this->post_type . "_columns", array($this, 'admin_columns'));
		// define filetypes for pv uploaded files
		add_filter('upload_mimes', array($this,'custom_upload_mimes'));
		
		
	}

	public function build_taxonomies() {  
			register_taxonomy(  
			'pv_type',  
			'pv',  // this is the custom post type(s) I want to use this taxonomy for
			array(  
				'hierarchical' => true,  
				'label' => 'Product Viewer',  
				'query_var' => true,  
				'rewrite' => true  
			)  
		);
	}

	public function custom_upload_mimes( $existing_mimes)  {
	
		// Add file extension 'extension' with mime type 'mime/type'
		$existing_mimes['extension'] = 'mime/type';
		 
		// change file type array for pv
		if($this->post_type == 'pv') {
		
			// all worpress file types get cleared
			$existing_mimes = '';
			
			// for pv only gif/jpg/png are allowed
			$existing_mimes["jpg|jpeg|jpe"] = "image/jpeg";
			$existing_mimes["gif"] = "image/gif";
			$existing_mimes["png"] = "image/png";
			$existing_mimes["bmp"] = "image/bmp";
			$existing_mimes["tif|tiff"]= "image/tiff";
		
		}	

		// and return the new full result
		return $existing_mimes;
	
	}
	
	// translation are loade for text-domain
	public function loadLangFile() {
		 
		load_plugin_textdomain('splendid-product-viewer', false, basename( dirname( __FILE__ ) ) . '/lang' );	
	}
	
	public function pv_admin_script() {
		
		// scripts for color picker
		wp_enqueue_script('jquery');
		wp_register_script('pv_color_picker', plugins_url('js/jquery.simple-color-picker.js', __FILE__), array("jquery"));
		wp_enqueue_script('pv_color_picker');
		
		wp_register_script('my_admin_scripts', plugins_url('js/pv-backend-scripts.js', __FILE__));
		wp_enqueue_script('my_admin_scripts');
	}
	
	public function pv_admin_style() {
	
		// style for color picker
		wp_register_style('pv_color_picker', plugins_url('css/jquery.simple-color-picker.css', __FILE__));
		wp_enqueue_style('pv_color_picker');
		wp_register_style('my_admin_style', plugins_url('css/my-admin-style.css', __FILE__));
		wp_enqueue_style('my_admin_style');
	}


	public function pv_register_productviewer() {

		$labels = array(
		
			'name' => _x('Product Viewer', 'products','splendid-product-viewer'),
			'menu_name' => _x('Product Viewer', 'pv', 'splendid-product-viewer'),
			'add_new' => _x('Add New Product', 'pv', 'splendid-product-viewer'),
			'add_new_item' => _x('Add New Product', 'product', 'splendid-product-viewer'),
			'edit_item' => _x('Edit Product', 'edit_product','splendid-product-viewer'),
		   

		);

		$args = array(

			'labels' => $labels,
			'menu_icon' => plugins_url() . '/splendid-product-viewer/img/150_glasses.png', 
			'hierarchical' => true,
			'description' => 'Product Viewer',
			'supports' => array('title', 'editor'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'taxonomies' => array('pv') 
			
		);

		if( is_admin() ) {
			register_post_type('pv', $args);
		}
	}
	
	function wpt_portfolio_icons() {    
	?>
		<style>
			.icon32-posts-pv {
				background-image: url("<?php echo plugins_url(); ?>/splendid-product-viewer/img/150_glasses_32x32.png") !important;
				background-size: 32px 32px !important;
				background-position: 0px 0px !important; 
				 
			}
		</style>
	<?php }
	
	
	public function admin_columns($columns) {
	
		$columns['pv_thumb'] = __('Product Thumbnail','splendid-product-viewer');
		return $columns;
	}
	
	public function admin_column_content($column) {
		
		global $post;
			
		if ($column == 'pv_thumb') {
		
			$customImages 		= get_post_custom( $post->ID );	
			$customImagePath 	= isset($customImages['pv_image_2'][0]) ? $this->pv_upload_path_url . ($customImages['pv_image_2'][0]) : '';
			
			if ( $customImagePath == '' ) {
				echo _e('<em>None set</em>','splendid-product-viewer');
			}
			echo '<img src="' . $customImagePath . '" width="50">';
		}
	}		
	
	public function add_post_enctype() {
		echo ' enctype="multipart/form-data"';
	}
	

	public function init_admin() {
		
		

		// new folder for products within the uploads folder
		if (!is_dir($this->pv_upload_path_sys)) {
			@mkdir($this->pv_upload_path_sys, 0777);
		} else if (!is_writeable($this->pv_upload_path_sys)) {
			@chmod($this->pv_upload_path_sys, 0777);
		}
	
		$count = 0;
			
		foreach ($this->uploaders as $uploader) {
		
			// I admit, this is a hack
			// in order to get a correct translation 
			// I have reset the label value within the array
			if($uploader['label'] == 'Product Image') {
			
				$uploader['label'] = __('Product Image','splendid-product-viewer');
				
			} else {

				$uploader['label'] = __('Product Thumbnail','splendid-product-viewer');

			}
		
			add_meta_box(	'pv_uploader_meta_box_' . $count, 
								$uploader['label'],
								array($this, 'pv_post_sidebar'), 
								$this->post_type, 
								'side', 		// 'side' could be change to 'normal'  -> metaboxes below textarea
								'core', 
								array($uploader['name'],$uploader['isThumbImg'])
							);
			
			$count ++;
		}
	}
				
	
	public function pv_post_sidebar( $post, $metabox ) {
	
		global $post_ID;

		$meta_info 	= $metabox['args'];
		$name 		= $meta_info[0];
		$thumb		= $meta_info[1];
			
		$path_to_img= get_post_meta($post_ID, $name, true);

		$url_to_img = $this->pv_upload_path_url . $path_to_img;
		
		$image	 	= $url_to_img;
		$color 		= get_post_meta($post_ID, 'pv_color', true) == '' ? '' : get_post_meta($post_ID, 'pv_color', true);
		$price 		= is_numeric(get_post_meta($post_ID, 'pv_price', true)) ? get_post_meta($post_ID, 'pv_price', true) : 0;
		$upload_error= get_post_meta($post_ID, 'upload_error', true);
			
		if(get_post_meta($post_ID, 'upload_error', true) == 1 && $thumb == false) {
		
			echo '<div><p style="color:red"> ' . __('Warning - in order to maintain the quality standards, the minimum image width cannot be less than 600px!','splendid-product-viewer') . '</p></div>';
			
		}
		

		?>

		<div id="pv_uploader_page_box">
			<?php
				if($path_to_img) {
			?>
			<div style="margin-bottom: 10px;">
				<img src="<?php echo $image; ?>" alt="Existing Post Image"/>			
			</div>
			<?php
			}
			?>
			<div class="pv_upload_new_form_<?php echo $name ?>" style="margin-bottom: 5px;">
				<div style="margin-bottom: 10px;">
			<?php
				if($path_to_img) {
			?>
					<div>
						<em><?php _e('Change Image','splendid-product-viewer') ?></em>:
					</div>
			<?php
			} else {
			?>
					<div>
						<em><?php _e('Upload','splendid-product-viewer') ?></em>:
					</div>
			<?php
			}
			?>
					<div>
						<input type="file" name="<?php echo $name ?>" />
					</div>
				</div>
				<input type="hidden" value="'<?php echo $upload_path ?>'" name="upload_path" />
				<input type="hidden" value="true" name="upload_file" />	
				<input type="hidden" value="false" name="upload_error" />
			</div>
			<?php if ($thumb == false) { ?>
			<div>
				<strong><?php _e('Product Price:','splendid-product-viewer'); ?></strong>
				<input type="text" name="pv_price" value="<?php echo $price ?>">
			</div>
			<ul> 
				<li>
					<strong><?php _e('Product Color:','splendid-product-viewer'); ?></strong>
					<input type="text" id="pv_color" name="pv_color" style="background:<?php echo $color ?>" value="<?php echo $color ?>" readonly />
				</li>
			</ul>
			<?php } ?>
		</div>
	<?php
	}

	
	public function pv_handle_upload_file($post_id=false) {
	
		$post_id 			= isset($_POST['post_ID']) ? $_POST['post_ID'] : false;
		$file_is_uploaded 	= isset($_POST['upload_file']) ? $_POST['upload_file']: false;
		$price				= isset($_POST['pv_price']) && is_numeric($_POST['pv_price']) ? $_POST['pv_price'] : 0;
		$color				= isset($_POST['pv_color']) ? $_POST['pv_color'] : false;
		$upload_path 		= $this->pv_upload_path_sys;
		
		update_post_meta($post_id, 'pv_price', $price);	
		
		if ($color) {
			update_post_meta($post_id, 'pv_color', $color);
		}
		update_post_meta($post_id, 'meta_id', $post_id);

		if ($file_is_uploaded) {
			
			
			foreach ($this->uploaders as $uploader) {
			
				$name 	= $uploader['name'];
				$label 	= $uploader['label'];
				
				// error messages getting reset
				delete_post_meta($post_id, 'upload_error', 1);
				delete_post_meta($post_id, 'upload_error', 2);
				
				if (isset($_FILES[$name]) && $_FILES[$name]['error'] == 0) {
				
					// save uploaded files
					$uploadedfile = $_FILES[$name];
		
					$movefile = $this->wp_handle_upload( $uploadedfile, array( 'test_form' => false ), null, $upload_path );
					if ( $movefile ) {
						_e('File is valid, and was successfully uploaded.\n','splendid-product-viewer');
					} else {
						_e('Possible file upload attack!\n','splendid-product-viewer');
					}
					
					// save all images with needed file sizes
					$image = wp_get_image_editor( $movefile['file'] );						
					
					// is image control
					if(! is_wp_error( $image ) ) {
					
						// generate an unique filename
						$date = new DateTime();
						$unique_file_ext =  $date->getTimestamp();

						$filename = $image->generate_filename( $unique_file_ext, ABSPATH.'wp-content/uploads/product_viewer/', NULL );
						
						
						
						$image->save($filename);
					
						// if main image has been changed
						if( $uploader['isThumbImg'] == false) {

							// Warning if the image is smaller the 600px as image cannot
							// be processed by multi-resize function
							$image_size = $image->get_size();
							$image_width = $image_size['width']; 
							
							// file dimension control and error msg set
							if($image_width < '600' && $uploader['isThumbImg'] == false)
							{
								// 1 = wrong size
								update_post_meta($post_id, 'upload_error', 1);
								return;
							} else {
								// 0 = no error
								update_post_meta($post_id, 'upload_error', 0);
							}
							
							// preparing new images with correct sizes
							$sizes_array = array (
								array ('width' => 600, 'height' => 320, 'crop' => true),
								array ('width' => 260, 'height' => 260, 'crop' => false)
							);
						
						// if thumbnail image has been changed
						} else {
							$sizes_array = array (
								array ('width' => 88, 'height' => 88, 'crop' => true)
							);
						}
						
						// resizing all images
						$resize = $image->multi_resize( $sizes_array );

						// set the correct file paths for thumbnail and main image
						$new_files = array();
						$new_file;
						
						foreach ($resize as $row) {
							echo "<p>";
							echo $row['file']."<br>";
							$new_file = $row['file'];
							$new_files[] = $row['file'];
							echo $row['width']."<br>";
							echo $row['height']."<br>";
							$new_files[] =  $row['height'];
							echo $row['mime-type']."<br>";
							echo "</p>";
						}
						
						// delete the original file
						unlink($movefile['file']);
						unlink($filename);
						
						// update the post metadata with the correct file names
						if ($post_id) {
							
							
							//var_dump($upload_path);
							
							// $url = explode('\\',$upload_path);
							// $url_prepare = array_slice($url, -1, 1, true);
							// $url = '/'. implode('/',$url_prepare) . $new_file;
							// $url2 = '/'. implode('/',$url_prepare) . $new_files[0];
							
							update_post_meta($post_id, $name, $new_file);
							
							if( $new_files[1] == 320 )
							{
								update_post_meta($post_id, 'main_image', $new_files[0]);
							}
						}	
					} 
				}
			}
		}
	}

	/** 
	 *  !!! OVERWRITE !!! the original wordpress upload function
	 * - CHANGED : new Param for custom $upload_path 
	 * - OTHER CHANGES : marked with _daa_
	 */
	 
	function wp_handle_upload( &$file, $overrides = false, $time = null, $upload_path = null ) {

	

		// The default error handler.
		if ( ! function_exists( 'wp_handle_upload_error' ) ) {
			function wp_handle_upload_error( &$file, $message ) {
				return array( 'error'=>$message );
			}
		}
		
		$file = apply_filters( 'wp_handle_upload_prefilter', $file );

		// You may define your own function and pass the name in $overrides['upload_error_handler']
		$upload_error_handler = 'wp_handle_upload_error';

		// You may have had one or more 'wp_handle_upload_prefilter' functions error out the file. Handle that gracefully.
		if ( isset( $file['error'] ) && !is_numeric( $file['error'] ) && $file['error'] )
			return $upload_error_handler( $file, $file['error'] );

		// You may define your own function and pass the name in $overrides['unique_filename_callback']
		$unique_filename_callback = null;

		// $_POST['action'] must be set and its value must equal $overrides['action'] or this:
		$action = 'wp_handle_upload';

		// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
		$upload_error_strings = array( false,
			__( "The uploaded file exceeds the upload_max_filesize directive in php.ini." ),
			__( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." ),
			__( "The uploaded file was only partially uploaded." ),
			__( "No file was uploaded." ),
			'',
			__( "Missing a temporary folder." ),
			__( "Failed to write file to disk." ),
			__( "File upload stopped by extension." ));

		// All tests are on by default. Most can be turned off by $overrides[{test_name}] = false;
		$test_form = true;
		$test_size = true;
		$test_upload = true;

		// If you override this, you must provide $ext and $type!!!!
		$test_type = true;
		$mimes = false;

		
		// Install user overrides. Did we mention that this voids your warranty?
		if ( is_array( $overrides ) )
			extract( $overrides, EXTR_OVERWRITE );

		// A correct form post will pass this test.
		if ( $test_form && (!isset( $_POST['action'] ) || ($_POST['action'] != $action ) ) )
			return call_user_func($upload_error_handler, $file, __( 'Invalid form submission.' ));

		// A successful upload will pass this test. It makes no sense to override this one.
		if ( $file['error'] > 0 )
			return call_user_func($upload_error_handler, $file, $upload_error_strings[$file['error']] );

		// A non-empty file will pass this test.
		if ( $test_size && !($file['size'] > 0 ) ) {
			if ( is_multisite() )
				$error_msg = __( 'File is empty. Please upload something more substantial.' );
			else
				$error_msg = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.' );
			return call_user_func($upload_error_handler, $file, $error_msg);
		}

		// A properly uploaded file will pass this test. There should be no reason to override this one.
		if ( $test_upload && ! @ is_uploaded_file( $file['tmp_name'] ) )
			return call_user_func($upload_error_handler, $file, __( 'Specified file failed upload test.' ));

		// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
		if ( $test_type ) {
			$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

			extract( $wp_filetype );

			// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
			if ( $proper_filename )
				$file['name'] = $proper_filename;

			if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
				return call_user_func($upload_error_handler, $file, __( 'Sorry, this file type is not permitted for security reasons.' ));

			if ( !$ext )
				$ext = ltrim(strrchr($file['name'], '.'), '.');

			if ( !$type )
				$type = $file['type'];
		} else {
			$type = '';
		}

		// _daa_ wp_upload_dir 'path' changed to my own path
		$pvCorrection  			= wp_upload_dir();
		$pvCorrection['path'] 	= $upload_path;

		// A writable uploads dir will pass this test. Again, there's no point overriding this one.
		if ( ! ( ( $uploads = $pvCorrection ) && false === $uploads['error'] ) )
			return call_user_func($upload_error_handler, $file, $uploads['error'] );

		$filename = wp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );

		// Move the file to the uploads dir
		$new_file = $uploads['path'] . "/$filename";

		if ( false === @ move_uploaded_file( $file['tmp_name'], $new_file ) ) {
			if ( 0 === strpos( $uploads['basedir'], ABSPATH ) )
				$error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
			else
				$error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];

			return $upload_error_handler( $file, sprintf( __('The uploaded file could not be moved to %s.' ), $error_path ) );
		}

		// Set correct file permissions
		$stat = stat( dirname( $new_file ));
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		// Compute the URL
		$url = $uploads['url'] . "/$filename";

		if ( is_multisite() )
			delete_transient( 'dirsize_cache' );
			
		return apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ), 'upload' );
	}

	
}