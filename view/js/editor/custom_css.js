var ve=ve||{};
(function(ve,$){
    ve.css={};
    var css=ve.css;
    css.init=function(){
        ve.add_action('element_updated',function(element){
           css.buidCustomCss(element);
        });
    };
    css.buidCustomCss=function(element){
        var custom_css=element.getParam('custom_css');
        var custom_css_class=element.getParam('custom_css_class');
        if(custom_css&&custom_css_class){
            custom_css='.'+custom_css_class+'{'+custom_css+'}';
        }
        ve.frame_view.addElementCustomStyle(custom_css);
    };
    css.init();
})(ve,jQuery);