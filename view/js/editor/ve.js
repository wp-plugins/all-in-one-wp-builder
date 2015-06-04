var ve=ve||{};
(function(ve,$){
    ve.setFrameSize = function(size) {
        var nav_height=$('#ve-topbar').height();
        var height = $(window).height() - nav_height;
        ve.$frame.width(size);
        ve.$frame_wrapper.css({top: nav_height});
        ve.$frame.height(height);

    };
    ve.onScreenResize = function(event, ui)
    {
        ve.topbar.onScreenResize(event, ui);
    };
    ve.saveScreenSize = function(event,ui){
        clearTimeout(ve.savingScreenSize);
        ve.savingScreenSize=setTimeout(function(){
            ve.savePostSetting('screen_size',ui.size.width);
        },1000);

    };
    ve.getElementSetting=function (id_base){
        return ve.all_elements[id_base]||{};
    };
    ve.getElementUpperLevel=function(level){
        if(!ve.all_elements_level){
            ve.all_elements_level=[];
            _.each(ve.all_elements,function(e){
                if(e.lv){
                    !ve.php.in_array(e.lv,ve.all_elements_level)&&ve.all_elements_level.push(e.lv);
                }
            });
            ve.all_elements_level.sort(function(a,b){return b-a});
        }
        //var upperlevel=level;
        return _.find(ve.all_elements_level,function(lv){
            if(lv<level){
                return lv;
            }
        });
        //return upperlevel;
    };

    ve.getElementView=function(element){
        var default_view_name=element.setting('container')?'ElementViewContainer':'ElementView';
        var view_name='ElementView'+'_'+element.get('id_base');
        ve.log('-looking for view: '+view_name);
        if(_.isObject(ve.element_views[view_name])){
            ve.log('--found view: '+view_name);
            return ve.element_views[view_name];
        }else{
            ve.log('--use default view: '+default_view_name);
            return ve.element_views[default_view_name];
        }
    };
    ve.getElement=function(may_be_element){
        if(may_be_element instanceof ve.Element){
            return may_be_element;
        }
        return ve.getElementById(may_be_element);
    };
    ve.getElementById=function(id){
        return ve.elements.get(id);
    };
    ve.getViewById=function(id){
        var element=ve.getElementById(id);
        if(element){
            return element.view;
        }
        return false;
    };
    ve.getChildren=function(parent){
        if(parent instanceof ve.Element){
            parent=parent.get('id');
        }
        return ve.elements.where({parent_id:parent});
    };
    ve.notifyParent = function(parent_id) {
        var parent = ve.getElementById(parent_id);
        parent && parent.view && parent.view.changed();
    };
    ve.notifyChildren = function (element){
        if(!element instanceof ve.Element){
            element=ve.getElementById(element);
        }
        if(element) {
            _.each(ve.elements.where({parent_id: element.get('id')}), function (child) {
                child.view.parent_view = element.view;
                child.view.parentChanged();
            }, this);
        }
    };
    ve.log=function(message){
        return console.log(message);
    };
    ve.ajax= function(data, data_type, url) {
        return this._ajax = $.ajax({
            url: url || ve.admin_ajax,
            type: 'POST',
            data: _.extend({ve_inline: true,post_id:ve.post_id}, data),
            dataType: data_type,
            context: this
        });
    };
    /*
        save setting to current user
     */
    ve.saveSetting=function(key,val){
        var setting=key;
        if(typeof key!="object"){
            (setting={})[key]=val;
        }
        _.extend(ve.settings,setting);
        ve.ajax({action:'ve_save_setting','settings':ve.settings});
    };
    ve.getSetting=function(k){
       if(ve.settings && ve.settings[k]){
           return ve.settings[k];
       }
        return false;
    };
    /**
     * save setting on current post
     */
    ve.savePostSetting=function(key,val){
        var setting=key;
        if(typeof key!="object"){
            (setting={})[key]=val;
        }
        _.extend(ve.post_settings,setting);
        ve.ajax({action:'ve_save_post_setting','settings':ve.post_settings});
    };
    ve.getPostSetting=function(k){
        if(ve.post_settings && ve.post_settings[k]){
            return ve.post_settings[k];
        }
        return false;
    };
    ve.mergeParams=function(oldParams,newParams){
        if(typeof oldParams != "object"){
            oldParams={};
        }
        _.extend(oldParams,newParams);
        return oldParams;
    };
    ve.ready=function(callback){
        if(typeof callback=='function') {
            ve.add_action('ready', callback);
        }
    };
}(ve,jQuery));