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

    var promoText = ["Get pro version with lots more features. Click here",
                     "Download the pro version now",
                     "Popups triples conversion rate. Click here to create it",
                     "Create popups and widgets on your site is easy. Click here to find out how",
                     "Does you site have popups? Create some now!",
                     "Don't let your visitor leave your site empty handed. Create a popup now",
                     "Do you know AIO WP Builder can create popups and widgets too? Find out how",
                     "Do you know you can create anything with AIO WP Builder? Here is how"
    ];

    var linkText = promoText[Math.floor(Math.random()*promoText.length)];
    var url = "http://allinonewpbuilder.com/?linkText=" + linkText;
    var link = '<a target="_blank" href="' + url + '">' + linkText + '</a>';
    jQuery(".promo-link").html(link);


    setInterval(function(){
        linkText = promoText[Math.floor(Math.random()*promoText.length)];
        url = "http://allinonewpbuilder.com/?linkText=" + linkText;
        link = '<a target="_blank" href="' + url + '">' + linkText + '</a>';
        jQuery(".promo-link").html(link);

    }, 9000);



});
