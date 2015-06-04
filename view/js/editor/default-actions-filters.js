/**
 * Created by Alt on 4/8/2015.
 */
/**
 * Ve actions
 */
ve.on('post_saved',function(){
    ve.panel.showMessage('Post saved!');
});
ve.on('ajax_form_done_ve_update_post',function(res,form,data){
    if(data.post_title){
        ve.update_title=true;
        ve.post_title=data.post_title;
    }
    if(res&&res.link){
        ve.topbar.$('#view_post_link').attr('href',res.link);
    }
});
ve.on('build_complete',function(elements){
    //build new elements
    /**_.each(elements,function(element){
        var parent=ve.getElement(element.get('parent_id'));
        if(parent&&parent.get('id_base')=='ve_col'){
            parent.view.updateColumnWidthInfo();
        }
    });
     **/
});
ve.on('build_element',function(updated){//element updated or added
    ve.setFrameSize();
    ve.frame_view.setSortable();
    ve.frame_view.loadScripts();
    ve.panel.refresh();
});
ve.on('element_rendered',function(element){
    //ve.log('rendered'+element);
});
ve.on('element_updated',function(element){
    if(element.view) {
        element.view.select();
        element.view.update();
        element.view.updateTree();

    }
    ve.panel.showMessage('Element Updated!',1000);
});
ve.onContentLoaded=function(){
    var rows=ve.elements.where({id_base:'ve_row'});
    _.each(rows,function(row){
        row.view.onContentLoaded();
    });
    var cols=ve.elements.where({id_base:'ve_col'});
    _.each(cols,function(col){
        col.view.onContentLoaded();
    });
};
ve.on('content_loaded',function(){//for initial load
    ve.onContentLoaded();
    ve.frame_view.render();
    ve.content_loaded=true;
    if(typeof window.ve_elements_script !='undefined') {
        _.each(window.ve_elements_script, function (v) {
            v&&ve.frame_view.doScripts(v);
        });
    }
    ve.panel.refresh();
});
ve.on('element_moved',function(element,from,to){
    //console.log('move:'+element+': '+from+'->'+to);
    ve.notifyParent(from);
    ve.notifyParent(to);

});
ve.on('element_deleted',function(element){
    ve.notifyParent(element.get('parent_id'));
});
/**
 VE filters
 */
ve.add_filter('element_params',function(params){
    _.each(params,function(v,k){
       if(!v){
           delete params[k];
       }
    });
    return params;
});