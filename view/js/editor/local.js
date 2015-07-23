/**
 * Created by luis on 7/23/15.
 */

jQuery(function(){

    jQuery(document).on("click", "[name='adv-bg-type']", function(){
        jQuery(".adv-bg").hide();

        if (jQuery(this).val() == "image")
        {
            jQuery(".adv-img-bg").fadeIn();
        } else
        {
            jQuery(".adv-solid-bg").fadeIn();
        }

    });

    jQuery(document).on("click", "[name=background]", function(){
        jQuery(".bg-type").hide();
        if (jQuery(this).val() == "solid")
        {
            jQuery(".solid-color").fadeIn();
        } else
        {
            jQuery(".image-bg").fadeIn();
        }
    });



});
