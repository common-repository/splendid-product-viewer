<?php
class pv_frontend {
	
	
	private $pv_upload_path_url;
	
	public function __construct() {
		
		$this->init();	
	}
	
	public function init() {
		
		$this->add_actions_filters_wp();
	}
	
	
	private function add_actions_filters_wp () {
		
		// Scripts to Frontend
		add_action('wp_enqueue_scripts', array($this,'pv_scripts'));
		
		// Add Styles to Frontend
		add_action('wp_enqueue_scripts', array($this,'pv_styles'));
		
		// add shortcodes
		add_action('init', array($this,'pv_add_shortcodes'));
		
		$upload_dir = wp_upload_dir();
		$this->pv_upload_path_url = $upload_dir['baseurl'] . '/splendid-product-viewer/';
	}	
	
	
	public function pv_add_shortcodes() {
		add_shortcode("splendid-product-viewer", array($this,"pv_display_viewer"));
	} 
	
	function pv_display_viewer($attr, $content) {

		extract(shortcode_atts(array('id' => ''), $attr));

		// cleaned data structure
		$pv_viewer_data = array();
		$colors 		= array();
		$slider_values 	= array();
		
		$main_image =	'';
		
		$type = 'pv';
		$args=array(
		  'post_type' => $type,
		  'post_status' => 'publish',
		  'posts_per_page' => -1,
		  'ignore_sticky_posts'=> 1
		);
		$my_query = null;
		$my_query = new WP_Query($args);
		
		if( $my_query->have_posts() ) {
		
		  while ($my_query->have_posts()) : $my_query->the_post(); 

			$data = array();
			
//printf( '<pre>%s</pre>', var_export( get_post(), true ) );
//printf( '<pre>%s</pre>', var_export( get_post_custom(), true ) );
	

			$pv_custom_post_meta = get_post_custom();
			$pv_posts = get_post();
			
			$data['pv_title'] = $pv_posts->post_title;
			$data['pv_content'] = $pv_posts->post_content;
		
			$data['pv_thumbnail_path'] = $pv_custom_post_meta['pv_image_2'][0];
			if($pv_custom_post_meta['main_image'][0] != '') {
				$main_image = $pv_custom_post_meta['main_image'][0];
			}
			$data['pv_main_image'] = $pv_custom_post_meta['main_image'][0];
			$data['pv_price'] = $pv_custom_post_meta['pv_price'][0] ? $pv_custom_post_meta['pv_price'][0] : 0;
			$data['pv_color'] = $pv_custom_post_meta['pv_color'][0];
			if($pv_custom_post_meta['pv_color'][0] != '') {
				$colors[] =  $pv_custom_post_meta['pv_color'][0];
			}
				
			$data['pv_id'] = $pv_custom_post_meta['meta_id'][0];
			$data['pv_price'] = $pv_custom_post_meta['pv_price'][0];
			$slider_values[] = $pv_custom_post_meta['pv_price'][0];
	
			
			// turn categories into a string
			$categories_string = "";
			
			$terms = wp_get_post_terms($pv_posts->ID, "pv_type");
			foreach ($terms as $termname) {  
					
				$categories_string .= $termname->name . ' ';
			} 	
			$data['categories'] = $categories_string; 	
			
			$pv_viewer_data[] = $data;
			
			endwhile;
		}
		
		// Restore global post data stomped by the_post().
		wp_reset_query();  
	
		// Avoiding multiple Color Icons in Frontend
		$colorsUnique	= array_unique($colors);
	    
		// Categories for the select box
		$args = array(
		  'taxonomy' => 'pv_type',
		  );
		$categories = get_categories($args);
		
		// javascript rangslider value for englisch format
		if(get_locale() == 'en_US' || get_locale() == 'en_GB') {
			$lang = 'en';
		} else if ( get_locale() == 'de_CH') {
			$lang = 'ch';
		}
		
		// only show content when data is available
		if(count($pv_viewer_data) > 0) {
		?>
		<?php // Value picked up by the price range slider javascript => the highest value found in the posts ?>
		<?php
		$return = "";
		$return .= '<div id="start-value-slider" data-lang="'.$lang.'" data-config="'.max($slider_values).'" />';
		
			
		$return .= '<div id="pv-container">';
			
		$return .= '<div id="main-img-holder">';
				
		$return .= '<img  src="'.$main_image.'" />';

		$return .= '</div>';
		$return .= '<div id="pv-controls">';
			
		$return .= '<div id="pv-color-boxes">';
	
		if( count($colorsUnique) > 0) {
		
		$return .= '<div id="pv-color-results"></div>';		
			
		$return .= '<p>'._x("Choose a color","splendid-product-viewer").'</p>';
			
		$return .=  '<ul id="ul-color-boxes">';			

		foreach($colorsUnique as $color) {

		$return .=  '<li>';
		$return .=  '<div class="pv-color" data-pvcolor="'.$color.'"  style="background:'.$color.'"></div>';
		$return .=  '</li>';
		
		}
		$return .= 	'<li>';
		$return .= 	'<div id="all-colors"><img src="'.plugins_url('splendid-product-viewer/img/refresh.png').'" /></div>';
		$return .= 	'</li>';
		$return .= 	'</ul>';
		
		}
				
		$return .= 	'</div>';
		$return .= 	'<div id="pv-select-category">'; 
		$return .= 	'<div id="group_select">';
        $return .= 	'<p>'._x("Product groups","splendid-product-viewer").'</p>';
		$return .= 	'<label>';
		$return .= 	'<select id="pv-categories-select" >';
		$return .= 	'<option selected="selected" value="-1">'._x("All","splendid-product-viewer").'</option>';
		
							foreach($categories as $cat) {
								
									$return .= '<option value="' . $cat->name . '">' . $cat->name . '</option>'; 
									//$cat->name . '<br />';
								
								//var_dump($cat);
							} 
							
		$return .= 	'</select>';
		$return .= 	'</label>';
		$return .= 	'</div>';
		$return .= 	'</div>';
		$return .= 	'</div>';
			
				if( max($slider_values) > 0) {
									
		$return .= 	'<div id="pv-price-slider">';
		$return .= 	'<div id="label-price">'._x("From&nbsp;USD <span id='price-from'></span><br />to&nbsp;USD&nbsp;<span id='price-to'></span>","splendid-product-viewer").'</span></div>';
		$return .= 	'<div id="holder-slider">';
		$return .= 	'<div id="pv-range-slider"></div>';
		$return .= 	'</div>';
        $return .= 	'</div>';
		
				}
			
			
			
		$return .= 	'<div id="pv-thumbnail-holder">';
		$return .= 	'<ul id="ul-pv-thumbs">';
			
					foreach($pv_viewer_data as $pv_data ) {							
						
						$price = is_numeric($pv_data['pv_price']) ? $pv_data['pv_price'] : 0;
					
					
						if(get_locale() == 'en_US' || get_locale() == 'en_GB') {
							$price_format = number_format($price, 2, '.', ',');
						} else {
							$price_format = number_format($price, 2, '.', '\'');
						}
						$currency = __('Price: USD&nbsp;','splendid-product-viewer');
					
					
		$return .= 	'<li>';
					
				
					$return .= 	'<div class="pv_thumb" 	data-title="'.$pv_data["pv_title"].'"'; 
					$return .= 	'data-loader="'.plugins_url("splendid-product-viewer/img/spinner.gif").'"'; 
					$return .= 	'data-content="'.$pv_data["pv_content"].'" ';
					$return .= 	'data-category="'.$pv_data["categories"].'"';
					$return .= 	'data-mainimage ="'.$this->pv_upload_path_url . $pv_data["pv_main_image"].'"';
					$return .= 	'data-color="'.$pv_data["pv_color"].'" ';
					$return .= 	'data-currency="'.$currency.'"'; 
					$return .= 	'data-price="'.$price.'"'; 
					$return .= 	'data-priceformated="'.$price_format.'"'; 
					$return .= 	'data-id="'.$pv_data["pv_id"].'"'; 
					$return .= 	'data-link="'.plugins_url().'/splendid-product-viewer/pv_details_page.php?id='.$pv_data["pv_id"].'">';
					$return .= 	'<img src="'.$this->pv_upload_path_url . $pv_data["pv_thumbnail_path"].'" />';
					$return .= 	'</div>';
					$return .= 	'</li>';
					}
					
					$return .= 	'</ul>';
					$return .= 	'<div id="pv-no-results">';
					$return .= 	 _x("No entries found.","splendid-product-viewer");
					$return .=	'<br /><a href="#" id="pv_search">'. _x("New search?","splendid-product-viewer").'<img src="'.plugins_url("splendid-product-viewer/img/refresh.png").'" /></a>';
					$return .= 	'</div>';
					$return .= 	'</div>';
					$return .= 	'</div>';
		
					return $return;
		}
	}	
	
	function pv_scripts() {

		global $post;

		wp_enqueue_script('jquery');

		wp_register_script('pv_range_slider', plugins_url('js/jquery.nouislider.js', __FILE__), array("jquery"));
		wp_enqueue_script('pv_range_slider');

		wp_register_script('pv_number_format', plugins_url('js/jquery.number.min.js', __FILE__));
		wp_enqueue_script('pv_number_format');
		
		wp_register_script('pv_frontend_scripts', plugins_url('js/pv-frontend-scripts.js', __FILE__));
		wp_enqueue_script('pv_frontend_scripts');

		$effect      = (get_option('fwds_effect') == '') ? "slide" : get_option('fwds_effect');
		$interval    = (get_option('fwds_interval') == '') ? 2000 : get_option('fwds_interval');
		$autoplay    = (get_option('fwds_autoplay') == 'enabled') ? true : false;
		$playBtn    = (get_option('fwds_playbtn') == 'enabled') ? true : false;
			$config_array = array(
				'effect' => $effect,
				'interval' => $interval,
				'autoplay' => $autoplay,
				'playBtn' => $playBtn
			);

//		wp_localize_script('slidesjs_init', 'setting', $config_array);
		
	}
	
	
	function pv_styles() {

		wp_register_style('pv_range_slider_style', plugins_url('css/jquery.nouislider.css', __FILE__));
		wp_enqueue_style('pv_range_slider_style');
		
		wp_register_style('my_frontend_style', plugins_url('css/my-frontend-style.css', __FILE__));
		wp_enqueue_style('my_frontend_style');
	}
}
