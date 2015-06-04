<?php
/**
 * Template Name: Ve Full Width
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?php
        wp_head();
        echo "<title>".get_the_title()."</title>";

    ?>
</head>
<body <?php body_class();?>>
<?php the_post();
$customStyle='';
if($width=ve_get_post_meta('screen_size')){
    $width = intval($width) - 52;
    $customStyle.='max-width:'.$width.'px;';
}
if($customStyle){
    $customStyle=sprintf(' style="%s"',$customStyle);
}
?>
<div class="ve-container"<?php echo $customStyle;?>>
    <?php the_content();?>
</div>
<?php wp_footer();?>

</body>
</html>
