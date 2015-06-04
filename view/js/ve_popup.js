var ve_popup=ve_popup||{};
(function(popup,$){
    popup.init=function(){
        popup.setSelector();
        popup.setVars();
        popup.setEvents();
    };
    popup.setVars=function(){

    };
    popup.setSelector=function(){
        popup.$popup=$('.ve-popup');
    };
    popup.setEvents=function(){
        popup.setStyles();
        popup.setOpen();
        $('body').on('click','.close-popup',function(e){
            e.preventDefault();
            var the_popup=$(this).closest('.ve-popup');
            popup.close(the_popup);
            return false;
        });
    };
    popup.setOpen=function(){
        popup.$popup.each(function() {
            var $popup = $(this),
                data = $popup.data('popup');
            if(data.open){
                switch (data.open){
                    case 'open_on_mouse_out':
                        $(document).on('mouseleave',function(){popup.open($popup)});
                        break;
                    case 'open_with_delay':
                        var delay=parseInt(data.delay)*1000;
                        setTimeout(function(){popup.open_once($popup)},delay);
                        break;
                }
            }
        }
        );
    };
    popup.setStyles=function(){
        popup.$popup.each(function(){
            popup.setStyle($(this));
        });
    };
    popup.setStyle=function($popup){
        var data=$popup.data('popup');

        if(!data){
            return ;
        }

        var offset={top:'',left:'',right:'',bottom:''};
        var margin={top:'',left:'',right:'',bottom:''};

        if(data.offset&&data.appearance!='center'){
            if(data.offset.top!==''){
                offset.top=data.offset.top;
            }
            if(data.offset.left!==''){
                offset.left=data.offset.left;
            }
            if(data.offset.right!==''){
                offset.right=data.offset.right;
            }
            if(data.offset.bottom!==''){
                offset.bottom=data.offset.bottom;
            }
        }

        $.each(margin,function(k,v){
            $popup.css('margin-'+k,v);
        });
        if(data.size){
            if(data.size.width!=='') {
                $popup.css('max-width',data.size.width + 'px');

            }

            data.size.height!==''&&$popup.css('max-height',data.size.height);
        }
        $popup.addClass('ve_popup_' + data.position);
    };
    popup.open_once=function(selector){
        var $the_popup=$(selector);
        if(!$the_popup.data('opened')){
            popup.open(selector);
        }
    };
    popup.open=function(selector){
        if(!isNaN(selector)){//is number
            selector='#ve-popup-'+selector;
        }
        $popup = $(selector);
        this.setStyle($popup);
        popup.close();//close all other popup

        $popup.removeClass('ve-hide').data('opened',true).show();
        var data=$popup.data('popup');
        if(data.position == 'center') {
            $popup.css('margin-left', -parseInt(data.size.width) / 2 + 'px');
            $popup.css('margin-top', -parseInt($popup.outerHeight()) / 2 + 'px');
        }
        if(data.offset)
        {
            if(data.offset.top != "")
                $popup.css('margin-top', "+="  +parseInt(data.offset.top));
            if(data.offset.right != "")
                $popup.css('margin-right', "+="  +parseInt(data.offset.right)+ '');
            if(data.offset.left != "")
                $popup.css('margin-left', "+="  +parseInt(data.offset.left)+ '');
            if(data.offset.bottom != "")
                $popup.css('margin-bottom', "+="  +parseInt(data.offset.bottom)+ '');
        }
    };
    popup.close=function(){
        popup.$popup.hide();
    };
    popup.init();
})(ve_popup,jQuery);