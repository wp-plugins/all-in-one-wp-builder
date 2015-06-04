/**
 * Created by Alt on 5/3/2015.
 */
var ve_front=ve_front||{};
(function(ve_front,$) {
    var VeButton = VeFront.extend({
        setup: function ($instance) {
            var ve_button=this;
            $instance.on('click',function(){
                ve_button.clickEvent($(this));
            });

        },
        clickEvent: function (button) {
            var link=button.data('link');
            var href=button.data('href');
            var target=button.data('target')||'_self';
            var popup=button.data('popup');
            if(!link){
                return ;
            }

            if(link=='popup'){
                popup&&ve_popup&&ve_popup.open(popup);
            }else{
                if(href){
                    var allow_open=true;
                    if(this.isIframe()){
                        if(target!='_blank'){
                            allow_open=false;
                        }
                    }

                    allow_open&&window.open(href,target);
                }
            }
        }

    });
    ve_front.button = new VeButton({el: '.ve_el-button'});
})(ve_front,jQuery);
