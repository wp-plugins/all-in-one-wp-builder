<?php
function ve_get_attachment_image( $params = array( 'post_id' => NULL, 'attach_id' => NULL, 'thumb_size' => 'thumbnail', 'class' => '' ) ) {
    if ( ( ! isset( $params['attach_id'] ) || $params['attach_id'] == NULL ) && ( ! isset( $params['post_id'] ) || $params['post_id'] == NULL ) ) return array();
    $post_id = isset( $params['post_id'] ) ? $params['post_id'] : 0;

    if ( $post_id ) $attach_id = get_post_thumbnail_id( $post_id );
    else $attach_id = $params['attach_id'];

    $thumb_size = $params['thumb_size'];
    $thumb_class = ( isset( $params['class'] ) && $params['class'] != '' ) ? $params['class'] . ' ' : '';

    global $_wp_additional_image_sizes;
    $thumbnail = '';
    if ( is_string( $thumb_size ) && ( ( ! empty( $_wp_additional_image_sizes[$thumb_size] ) && is_array( $_wp_additional_image_sizes[$thumb_size] ) ) || in_array( $thumb_size, array( 'thumbnail', 'thumb', 'medium', 'large', 'full' ) ) ) ) {
        $thumbnail = wp_get_attachment_image( $attach_id, $thumb_size, false, array( 'class' => $thumb_class . 'attachment-' . $thumb_size ) );
    } elseif ( $attach_id ) {
        if ( is_string( $thumb_size ) ) {
            preg_match_all( '/\d+/', $thumb_size, $thumb_matches );
            if ( isset( $thumb_matches[0] ) ) {
                $thumb_size = array();
                if ( count( $thumb_matches[0] ) > 1 ) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][1]; // height
                } elseif ( count( $thumb_matches[0] ) > 0 && count( $thumb_matches[0] ) < 2 ) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][0]; // height
                } else {
                    $thumb_size = false;
                }
            }
        }
        if ( is_array( $thumb_size ) ) {
            // Resize image to custom size
            $p_img = ve_image_resize( $attach_id, null, $thumb_size[0], $thumb_size[1], true );
            $alt = trim( strip_tags( get_post_meta( $attach_id, '_wp_attachment_image_alt', true ) ) );
            $attachment = get_post( $attach_id );
            if ( empty( $alt ) ) {
                $alt = trim( strip_tags( $attachment->post_excerpt ) ); // If not, Use the Caption
            }
            if ( empty( $alt ) )
                $alt = trim( strip_tags( $attachment->post_title ) ); // Finally, use the title
            if ( $p_img ) {
                $thumbnail = '<img class="' . $thumb_class . '" src="' . $p_img['url'] . '" width="' . $p_img['width'] . '" height="' . $p_img['height'] . '" alt="' . $alt . '" />';
            }
        }
    }
    $p_img_large = wp_get_attachment_image_src( $attach_id, 'large' );
    return array( 'thumbnail' => $thumbnail, 'p_img_large' => $p_img_large );
}
function ve_image_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
    $image_src=array();
    $actual_file_path='';
    if ( $attach_id ) {
        $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
        $actual_file_path = get_attached_file( $attach_id );
    } else if ( $img_url ) {
        $file_path = parse_url( $img_url );
        $actual_file_path = rtrim( ABSPATH, '/' ) . $file_path['path'];
        $orig_size = getimagesize( $actual_file_path );
        $image_src[0] = $img_url;
        $image_src[1] = $orig_size[0];
        $image_src[2] = $orig_size[1];
    }
    $file_info = pathinfo( $actual_file_path );
    $extension = '.' . $file_info['extension'];

    // the image path without the extension
    $no_ext_path = $file_info['dirname'] . '/' . $file_info['filename'];

    $cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;

    // checking if the file size is larger than the target size
    // if it is smaller or the same size, stop right here and return
    if ( $image_src[1] > $width || $image_src[2] > $height ) {

        // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
        if ( file_exists( $cropped_img_path ) ) {
            $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
            $vt_image = array(
                'url' => $cropped_img_url,
                'width' => $width,
                'height' => $height
            );
            return $vt_image;
        }

        // $crop = false
        if ( $crop == false ) {
            // calculate the size proportionaly
            $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
            $resized_img_path = $no_ext_path . '-' . $proportional_size[0] . 'x' . $proportional_size[1] . $extension;

            // checking if the file already exists
            if ( file_exists( $resized_img_path ) ) {
                $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

                $vt_image = array(
                    'url' => $resized_img_url,
                    'width' => $proportional_size[0],
                    'height' => $proportional_size[1]
                );
                return $vt_image;
            }
        }

        // no cache files - let's finally resize it
        $img_editor = wp_get_image_editor( $actual_file_path );

        if ( is_wp_error( $img_editor ) || is_wp_error( $img_editor->resize( $width, $height, $crop ) ) ) {
            return array(
                'url' => '',
                'width' => '',
                'height' => ''
            );
        }

        $new_img_path = $img_editor->generate_filename();

        if ( is_wp_error( $img_editor->save( $new_img_path ) ) ) {
            return array(
                'url' => '',
                'width' => '',
                'height' => ''
            );
        }
        if ( ! is_string( $new_img_path ) ) {
            return array(
                'url' => '',
                'width' => '',
                'height' => ''
            );
        }

        $new_img_size = getimagesize( $new_img_path );
        $new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

        // resized output
        $vt_image = array(
            'url' => $new_img,
            'width' => $new_img_size[0],
            'height' => $new_img_size[1]
        );
        return $vt_image;
    }

    // default output - without resizing
    $vt_image = array(
        'url' => $image_src[0],
        'width' => $image_src[1],
        'height' => $image_src[2]
    );
    return $vt_image;
}
function ve_element_editing(){
    return defined('VE_EL_EDITING')&&VE_EL_EDITING;
}