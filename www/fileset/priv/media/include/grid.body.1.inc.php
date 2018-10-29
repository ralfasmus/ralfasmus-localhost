<script type="text/javascript">

    jQuery(document).ready(function(){

        jQuery("#gallery").unitegallery({
            gallery_theme: "grid",

            grid_num_cols:3,
            gridpanel_vertical_scroll:false,
            gridpanel_grid_align: "top",

            gallery_width:1024,							//gallery width
            gallery_height:768,							//gallery height

            /* Thumbnail size */
            thumb_width:120,
            thumb_height:70,
            /* no sliders */
            /*
            slider_enable_arrows:false,
            slider_enable_progress_indicator:false,
            slider_enable_play_button:false,
            slider_enable_fullscreen_button:false,
            slider_enable_zoom_panel:false,
            slider_enable_text_panel:false,
            strippanel_enable_handle:false,
            gridpanel_enable_handle:false,
            */

            // Title Options:
            tile_textpanel_padding_top:3,		 	//textpanel padding top
            tile_textpanel_padding_bottom:3,	 	//textpanel padding bottom
            tile_textpanel_padding_right: 5,	 	//cut some space for text from right
            tile_textpanel_padding_left: 5,	 	//cut some space for text from left
            tile_enable_textpanel:true,
            tile_textpanel_title_font_size:8,	 //textpanel title font size. if null - take from css
            tile_textpanel_title_color: 'rgba(100,100,100, 0.7)',		 //textpanel title color. if null - take from css
            tile_textpanel_title_text_align: "left",
            tile_textpanel_always_on:true,
            tile_textpanel_bg_opacity: 0.00,		 	//textpanel background opacity
            tile_textpanel_bg_color:"#000000",	 	//textpanel background color
            tile_textpanel_title_bold:false,			 //textpanel title bold. if null - take from css
            tile_textpanel_css_title:{},			 //textpanel additional css of the title

            //slider options:

            slider_scale_mode: "fit",					//fit: scale down and up the image to always fit the slider
            //down: scale down only, smaller images will be shown, don't enlarge images (scale up)
            //fill: fill the entire slider space by scaling, cropping and centering the image
            slider_scale_mode_media: "fit",			//fit, down, full scale mode on media items
            slider_scale_mode_fullscreen: "fit",		//fit, down, full scale mode on fullscreen.

            slider_textpanel_always_on: false,
            theme_panel_position: "left",
        });
    });

</script>