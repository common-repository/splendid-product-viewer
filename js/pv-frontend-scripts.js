/* 
    Document   : frontend.js
    Author     : Alberto D'Angelo
    E-Mail     : albertodangelo@outlook.com
    Description: Javascript Splendid Product Viewer frontend
*/

jQuery(document).ready( function() {
    
			
    jQuery.ajaxSetup ({
        // Ajax cache turned off (Problem with IE)
        cache: false
    });
    
	var pvULthumbs 			= jQuery('#ul-pv-thumbs'),
		priceSlider 		= jQuery('#slider-range'),
		pvULcolorBoxes 		= jQuery('#ul-color-boxes'),
		pvImgHolder 		= jQuery('#main-img-holder'),
		pvSelectCategories 	= jQuery('#pv-categories-select'),
		pvBgColorBoxes	 	= jQuery('#pv-color-results'),
		sliderPriceLow 		= 0,
		sliderPriceHigh 	= jQuery('#start-value-slider').data('config') + 10,
        langFormat		 	= jQuery('#start-value-slider').data('lang'),
		resultCatFilter 	= '-1',
		resultColorFilter 	= '-1';
		
		
	var pvFrontendScripts = {
	

		
		init: function() {
			this.clickThumbs();
			this.filterThumbs();
			this.activateSlider();
			this.selectCatFilter();
			this.clickColors();
			this.newSearch();
			
			data = {
						title : jQuery('.pv_thumb').data('title'),
						content : jQuery('.pv_thumb').data('content'),
						main_image : jQuery('.pv_thumb').data('mainimage'),
						price : jQuery('.pv_thumb').data('price'),
						currency : jQuery('.pv_thumb').data('currency'),
						loader : jQuery('.pv_thumb').data('loader'),
						priceformated : jQuery('.pv_thumb').data('priceformated')
			};
			
			
			
			
			
			var href = jQuery('.pv_thumb').data('link');
			jQuery('.pv_thumb img').first().css( {"border": "3px solid orange", "height":"90"});
			
			pvFrontendScripts.loadMainImage(href,data);
		},
	
		clickThumbs: function() {
   
			
   
            var divTags = pvULthumbs.children().find('.pv_thumb');
            
            jQuery.each( divTags, function( key, value ) {   
   
                jQuery(this).on('click',function(e){ 
                    
					jQuery(".pv-loader").fadeIn();
					
                    jQuery.each( divTags, function(key, value) {
                         jQuery(this).children(1).css( {"border": "3px solid #000", "height":"90"});
                    } )
        
					data = {
						title : jQuery(this).data('title'),
						content : jQuery(this).data('content'),
						main_image : jQuery(this).data('mainimage'),
						price : jQuery(this).data('price'),
						currency : jQuery(this).data('currency'),
						loader : jQuery(this).data('loader'),
						priceformated : jQuery(this).data('priceformated')
					};
                    jQuery(this).children(1).css( {"border": "3px solid orange", "height":"90"});
					var href = jQuery(this).data('link');
					
					pvFrontendScripts.loadMainImage(href,data);
                });
             
            });
			
        },
		
		loadMainImage: function(href,data) {
			pvImgHolder.load( href + ' #main-img-holder', data, function(){
				jQuery(".pv-loader").fadeOut();
			}); 
			
		},
		
		loadFirstThumb: function () {
			
			var firstThumb 		= jQuery('#ul-pv-thumbs').find('li .pv_thumb:visible:first');
			var firstThumbImg 	= jQuery('#ul-pv-thumbs').find('li .pv_thumb:visible:first img');
				
				data = {
					title : firstThumb.data('title'),
					content : firstThumb.data('content'),
					main_image : firstThumb.data('mainimage'),
					price : firstThumb.data('price'),
					currency : firstThumb.data('currency'),
					loader : firstThumb.data('loader'),
					link: firstThumb.data('link'),
					priceformated : firstThumb.data('priceformated')
				};
				jQuery('.pv_thumb img').css( {"border": "none"});
				firstThumbImg.css( {"border": "3px solid orange", "height":"90"});

				var href = firstThumb.data('link');
				pvFrontendScripts.loadMainImage(href,data);
		},
		
		activateSlider: function () {
    
           	// Wait until the document is ready.
			jQuery(function(){

				if(langFormat == 'en') {
					var separator_000 = ',';
				} else {
					var separator_000 = ".";
				} 
				if (langFormat == 'ch') {
					var separator_000 = "\'";
				}
				
				jQuery("#price-from").text(sliderPriceLow);
				jQuery("#price-to").text(sliderPriceHigh).number( true, 0, '.', separator_000 );
			
				// Run noUiSlider
				jQuery('#pv-range-slider')
				.noUiSlider({
					 range: [sliderPriceLow,sliderPriceHigh]
					,start: [sliderPriceLow,sliderPriceHigh]
					,handles: 2
					,behaviour: "drag"
					,connect: true
					,step: 1
					,serialization: {
						resolution: 1
					}
					,slide: function(){
							
							var data = this.val();
							sliderPriceLow = data[0];
							sliderPriceHigh = data[1];
							
							jQuery("#price-from").text(sliderPriceLow).number( true, 0, '.', separator_000 ); 
							jQuery("#price-to").text(sliderPriceHigh).number( true, 0, '.', separator_000 );
							
							pvFrontendScripts.filterThumbs();
						
					}
				});
				
			});
            
        },
		filterThumbs: function() {
	
			var divTags = pvULthumbs.children().find('.pv_thumb');
				
			jQuery.each( divTags, function( key, value ) {   
		
				var price 	= jQuery(this).data('price'),
				show	= false; 
				
				var catResult = jQuery(this).data('category');
				
				// Thumbnails data and results from select box are getting
				// compared with regex
				if (catResult.match(resultCatFilter)) {
					show 	= true;
				}
				else {
					show	= false;
				}	
				
				// thumbnails get shown or hidden as per filter settings
				if( ( resultColorFilter == jQuery(this).data('color') || resultColorFilter == -1) &&
					(show == true || resultCatFilter == -1) &&
					(  price >= sliderPriceLow && price <  sliderPriceHigh  )
				) {
			          
					jQuery(this).show();
				   
				} else {
					jQuery(this).hide();
				}	
			});
			if( jQuery('.pv_thumb > :hidden').length >= divTags.length) {
			//	console.log('stop');
				jQuery("#pv-no-results").show();
			} else {
			//	console.log('okay');
				jQuery("#pv-no-results").hide();
			}
		},
		selectCatFilter: function () {
			pvSelectCategories.on('change',function(e){
				resultCatFilter = pvSelectCategories.val();
				pvFrontendScripts.filterThumbs();
				pvFrontendScripts.loadFirstThumb();
				
			});
			
		},
		clickColors: function() {
			
			var divTags = pvULcolorBoxes.children().find('.pv-color');
            var btn		= pvULcolorBoxes.children().find('button');
			
			jQuery("#all-colors").on('click', function(){
				resultColorFilter = -1;
				pvBgColorBoxes.css({'background':'transparent','border':'none'});	
				pvFrontendScripts.filterThumbs();
			});
			
            jQuery.each( divTags, function( key, value ) {   
   
                jQuery(this).on('click',function(e){ 
					
					resultColorFilter = jQuery(this).data('pvcolor');
					pvBgColorBoxes.css({'background':resultColorFilter,'border':'3px solid #000'});	
					pvFrontendScripts.filterThumbs();
                });
             
            });			
		},
		newSearch: function() {
				jQuery('#pv_search').on('click', function(event){
					event.preventDefault();
					window.location.reload(true); 
					
				});
		}
	}
	
	pvFrontendScripts.init();
   
});	