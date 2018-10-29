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