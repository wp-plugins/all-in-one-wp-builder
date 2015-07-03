<?php
/**
 * Template Name: Ve Full Width
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?php if(!get_theme_support('title-tag')){?>
    <title><?php wp_title();?></title>
    <?php }?>
    <?php wp_head();?>
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
