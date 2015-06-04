<?php
function fieldAttachedImages( $att_ids = array() ) {
    $output = '';

    foreach ( (array)$att_ids as $th_id ) {
        $thumb_src = wp_get_attachment_image_src( $th_id, 'thumbnail' );
        if ( $thumb_src ) {
            $thumb_src = $thumb_src[0];
            $output .= '
			<li class="added">
				<img rel="' . $th_id . '" src="' . $thumb_src . '" />
				<a href="#" class="icon-remove"></a>
			</li>';
        }
    }
    if ( $output != '' ) {
        return $output;
    }
}
function ve_select_image_size(){
    get_intermediate_image_sizes();
}