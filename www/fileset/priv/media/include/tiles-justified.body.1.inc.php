<script type="text/javascript">

    jQuery(document).ready(function(){

        jQuery("#gallery").unitegallery({
            tiles_justified_space_between:0,
            tiles_type: "justified",

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

            /* slider positions */
            slider_arrow_right_align_hor: "left",
            slider_arrow_left_align_vert: "top",
            slider_arrow_right_align_vert: "top",
            slider_arrow_left_offset_hor:11,
            slider_arrow_right_offset_hor:41,
            slider_arrow_left_offset_vert:10,
            slider_arrow_right_offset_vert:10,
            slider_play_button_align_hor: "right",
            slider_fullscreen_button_align_hor: "right",
            slider_zoompanel_offset_vert:45,
            slider_zoompanel_offset_hor:10,
            slider_progress_indicator_align_hor: "right",
            slider_progress_indicator_offset_hor:60,
            slider_progress_indicator_offset_vert:5,
        });
    });

</script>