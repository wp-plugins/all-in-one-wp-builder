var ve_popup=ve_popup||{};
(function(popup,$){
    popup.init=function(){
        popup.setSelector();
        popup.setVars();
        popup.setEvents();
    };

    popup.setVars=function(){
        var ve_storage=$.initNamespaceStorage('vepu');
        popup.data= ve_storage.localStorage;
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
        popup.setDone();
    };
    popup.setOpen=function(){
        popup.$popup.each(function() {
            var $popup = $(this),
                data = $popup.data('popup');
            if(data.open){
                switch (data.open){
                    case 'open_on_mouse_out':
                        $(document).on('mouseleave',function(){popup.open($popup,{inactive:300})});
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
    popup.setDone=function(){
        $(document).on('click','.ve-popup-done',function(e){
            var $popup=$(this).closest('.ve-popup');
            var popup_id=$popup.data('popup-id');
            console.log (popup_id);
            if(popup_id){
                popup.data.set('popup_'+popup_id+'_done',true);
            }

        });
    };
    popup.isDone=function(selector){
        if(!isNaN(selector)){//is number
            selector='#ve-popup-'+selector;
        }
        var $popup = $(selector);
        var popup_id=$popup.data('popup-id');
        return popup.data.get('popup_'+popup_id+'_done');
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
            if(data.size.width) {
                $popup.css('max-width',data.size.width + 'px');
            }
            data.size.height&&$popup.css('max-height',data.size.height);
        }
        $popup.addClass('ve_popup_' + data.position);
    };
    popup.open_once=function(selector){
        if(popup.showing)
            return false;
        var $the_popup=$(selector);
        if(!$the_popup.data('opened')){
            popup.open(selector);
        }
    };
    popup.open=function(selector,extraData){
        if(popup.showing)
            return false;
        if(!isNaN(selector)){//is number
            selector='#ve-popup-'+selector;
        }
        var $popup = $(selector);
        if($popup.length<1){
            console.error('could not open popup: '+selector);
            return false;
        }
        if(popup.isDone(selector)){//popup done, don't open again
            return false;
        }
        extraData=extraData||{};
        var data=$popup.data('popup'),
            popup_id=$popup.data('popup-id');
        if(extraData){
            _.extend(data,extraData);
        }
        if(data.inactive){
            var last_open=popup.data.get('last_open_'+popup_id);
            var inactive=data.inactive*86400000;
            var inactive_time=last_open+inactive-Date.now();
            if(inactive_time>0){
                console.info('popup is inactive!'+inactive_time);
                return false;
            }
        }
        this.setStyle($popup);
        popup.close();//close all other popup

        $popup.removeClass('ve-hide').data('opened',true).show();
        if(data.animation){
            var $animationPart=$popup.find('.ve-popup-wrapper');
            var animationClass='veani-'+data.animation+' veani-animated';
            $animationPart.addClass(animationClass).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $(this).removeClass(animationClass);
            });
        }
        popup.showing = true;
        if(popup_id){
            popup.data.set('last_open_'+$popup.data('popup-id'),Date.now());
        }

        if(data.position == 'center') {
            var width=parseInt(data.size.width)||$popup.outerWidth();
            $popup.css('margin-left', -width/ 2 + 'px');
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
        popup.showing = false;
        popup.$popup.hide();
    };
    popup.init();
})(ve_popup,jQuery);