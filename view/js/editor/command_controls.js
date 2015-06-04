/**
 * Created by alt on 3/11/2015.
 */
(function(ve,$){
    ve.controller={
        event:_.extend({}, Backbone.Events),
        init:function(){
            _.bindAll(this,'dispatch');
            this.event.listenTo(ve.command,'change',this.dispatch);
            $.Shortcuts
                .add({
                mask: 'Ctrl+C',
                handler: function() {
                    var selected=ve.content_view.getSelected();
                    if(selected){
                        ve.command.set({command:'copy',element:selected})
                    }
                }
                })
                .add({
                    mask: 'Ctrl+X',
                    handler: function() {
                        var selected=ve.content_view.getSelected();
                        if(selected){
                            ve.command.set({command:'cut',element:selected})
                        }
                    }
                })
                .add({
                    mask: 'Ctrl+V',
                    handler: function() {
                        var selected=ve.content_view.getSelected();
                        if(selected){
                            ve.command.set({command:'paste',element:selected,uid:VES4()})
                        }
                    }
                })
                .add({
                    mask: 'delete',
                    handler: function() {
                        ve.command.set({command:'delete',element:'',uid:VES4()});
                    }
                })
                .add({
                    mask: 'Ctrl+S',
                    prevent:true,
                    enableInInput:true,
                    handler: function(e) {
                        if(e&& e.target&&$(e.target).closest('#ve-element-form').length){
                            $(e.target).closest('#ve-element-form').submit();
                        }else {
                            ve.command.set({command: 'save', time: ve.time()})
                        }
                    }
                })
                .add({
                    mask: 'esc',
                    prevent:true,
                    enableInInput:true,
                    handler: function(e) {
                        ve.content_view.clearSelected();
                        ve.content_view.clearCutting();
                        ve.clipboard.reset();
                        ve.panel.hide();
                        ve.command.set({command:'',element:''},{silent:true});
                    }
                })
            ;
        },
        sanitize_action:function(action){
            if(_.isString(action)){
                action=ve.php.str_replace(['-','_'],' ',action);
                action=ve.php.ucwords(action);
                action=ve.php.lcfirst(action);
                action=ve.php.str_replace(' ','',action);
            }
            return action;
        },
        /**
         * @param cmd
         */
        dispatch:function(cmd){
            var action=cmd.get('command');
            action=this.sanitize_action(action);
            action=action+'Action';
            ve.log('dispatching:'+action);
            if(typeof this[action] == "function"){

                this[action](cmd);
            }

        },
        copyAction:function(cmd){
            ve.clipboard.push(cmd.clone());
        },
        pasteAction:function(cmd){
            var from=ve.clipboard.last();
            if(!from){//at least one element from clipboard to paste
                return false;
            }
            if(from.get('command')=='cut'){
                console.log('move element: '+from.get('element')+' -> '+cmd.get('element'));
                this.move_element(from.get('element'),cmd.get('element'))&&ve.clipboard.reset();
            }
            if(from.get('command')=='copy'){
                console.log('copy element: '+from.get('element')+' -> '+cmd.get('element'));
                this.copy_element(from.get('element'),cmd.get('element'));
            }
        },
        cutAction:function(cmd){
            if(element=ve.getElement(cmd.get('element'))){
                element.view.beingCut();
            }
            ve.clipboard.push(cmd.clone());
        },
        editAction:function(cmd){
            ve.panel.loadElementForm(cmd.get('element'));
        },
        saveAction:function(cmd){
            ve.the_editor.save();
        },
        selectAction:function(cmd){
            console.log('select');
            if(!cmd.get('multi')) {
                //ve.panel.loadElementForm(cmd.get('element'));
            }else{
                ve.panel.loadMultiForm();
            }
        },
        duplicateAction:function(cmd){
            var element;
            if(element=ve.getElement(cmd.get('element'))){
                this.duplicate_element(element);
            }
        },
        addAction:function(cmd){

            var from=ve.getElementSetting(cmd.get('from'));
            var to=ve.getElementById(cmd.get('to'));
            ve.log(from.id_base+'->'+cmd.get('to'));
            var editor=new ve.Editor();
            var atts;
            if(to) {
                if (!from.container) {
                    atts = {params: from.defaults, id_base: from.id_base, parent_id: cmd.get('to')};

                } else {
                    if (from.id_base == 've_col') {
                        var params=from.defaults;
                        params.width=cmd.get('width');

                        atts = {
                            params: params,
                            id_base: from.id_base,
                            parent_id: to.get('id')
                            //place_after_id: cmd.get('to')
                        };
                    }

                    if (from.id_base == 've_row') {
                        return this.add_row(to);
                    }

                }
            }else{
                if(from.id_base=='ve_row'||from.id_base=='ve_col'){
                    return this.add_row();
                }else{
                    return this.add_row_content(from);
                }
            }
            if(atts){
                editor.create(atts);
                editor.render();
            }

        },
        updateAction:function(cmd){
            var element=ve.getElement(cmd.get('element'));
            var newParams=cmd.get('params');
            //var params=element.get('params');
            //ve.mergeParams(params,newParams);
            element.set('params',newParams);
            ve.the_editor.update(element);
        },
        deleteAction:function (cmd){
            var deleted=false;
            if(element=ve.getElementById(cmd.get('element'))){//from mouse first
                if(this.delete_element(element)){
                    deleted=true;
                }
            }else{//try to get all selected elements and process
                if(this.delete_element(ve.content_view.getSelectedElements())){
                    deleted=true;
                }

            }
            if(deleted){
                ve.panel.hideForm();
            }
        },
        add_row:function(to){
            var placeAfterId=null;
            if(to&&to.get('parent_id')){
                while(to.get('parent_id')){
                    to=ve.getElementById(to.get('parent_id'));
                }
                placeAfterId=to.get('id');
            }

            var editor=new ve.Editor();
            editor.create({id_base:'ve_row',place_after_id:placeAfterId})
                .create({id_base:'ve_col',parent_id:editor.lastID()})
                .render();
        },
        add_row_content:function(e){
            var editor=new ve.Editor();
            editor.create({id_base:'ve_row'})
                .create({id_base:'ve_col',parent_id:editor.lastID()})
                .create({params: e.defaults, id_base: e.id_base, parent_id: editor.lastID()})
                .render();
        },
        duplicate_element:function(e){
            if(e) {

                var editor = new ve.Editor();
                editor.createFromElement(e);
                editor.render();
            }

        },
        copy_element:function(from,to){
            var editor=new ve.Editor();
            if(from==to){
                console.error('copy to same destination');
                return false;
            }
            from=ve.getElement(from);
            to=ve.getElement(to);
            if(!from.setting('container')){
                //copying element to container
                if(to.setting('container')) {
                    if (to.setting('container_element')) {//if it can contain element
                        editor.create({
                            id_base: from.get('id_base'),
                            parent_id: to.get('id'),
                            params: from.get('params')
                        }).render();
                    } else {//or create col to wrap it
                        editor.create({id_base:'ve_col',parent_id:to.get('id')})
                            .create({
                                id_base: from.get('id_base'),
                                parent_id: editor.lastID(),
                                params: from.get('params')
                            }).render();
                    }
                }
            }else{//copy from container
                if(from.setting('lv')<=to.setting('lv')){//copy a row to row or col
                    console.error('cannot copy a container to same level or lower level child');
                    return false;
                }
                if(to.get('id_base')=='ve_row'){
                    editor.create({
                        id_base: from.get('id_base'),
                        parent_id: to.get('id'),
                        params: from.get('params')
                    });
                    var last_id=editor.lastID();
                    var children=ve.getChildren(from);
                    _.each(children,function(e){
                        editor.create({
                            id_base: e.get('id_base'),
                            parent_id: last_id,
                            params: e.get('params')
                        })
                    });
                    editor.render();
                }

            }

        },
        move_element:function(from,to){
            from=ve.getElement(from);
            to=ve.getElement(to);
            if(!from||!to){
                return false;
            }
            if(from.closest(to)){
                ve.content_view.clearCutting();
                return true;
            }
            if(ve.getElementUpperLevel(from.setting('lv'))==to.setting('lv')){
                var parent_id=from.get('parent_id');
                from.set('parent_id',to.get('id'));
                ve.do_action('element_moved',from.get('id'),parent_id,to.get('id'));//move: element, from, to
                ve.content_view.clearCutting();
                return true;
            }
        },
        delete_element:function(element){
            if(_.isArray(element)){
                if(element.length>1) {
                    if (!confirm('Are you sure to delete selected elements')) {
                        return false;
                    }
                    _.each(element, function (e) {
                        var _e;
                        if (_e = ve.getElementById(e)) {
                            _e.destroy();
                            ve.do_action('element_deleted',_e);//destroy only remove model from collection, model still can be use
                        }
                    });
                    return true;
                }else{
                    element=ve.getElement(element);
                }
            }
            if(element instanceof ve.Element) {
                if (!confirm('Are you sure to delete ' + element.setting('name'))) {
                    return false;
                }
                element.destroy();
                ve.do_action('element_deleted',element);//destroy only remove model from collection, model still can be use
                return true;
            }

        },
        commandDisabled:{
            row:{
                paste:function(){
                    var last=ve.clipboard.lastElementIdBase();
                    if(last&&last!='ve_row'){
                        return false;
                    }
                    return true;
                }
            },
            col:{
                paste:function(){
                    var last=ve.clipboard.lastElementIdBase();
                    if(last&&last!='ve_row'&&last!='ve_col'){
                        return false;
                    }
                    return true;
                }
            },
            element:{

            }
        }



    };

})(ve,jQuery);


(function(ve,$){
    var Command=Backbone.Model.extend({
        defaults:function () {
            return {
                command:'no-command',
                element:''
            };
        }
    });
    var Clipboard = Backbone.Collection.extend({
        model: Command,
        maxSize:2,
        sync: function () {
            return false;
        },
        initialize:function(){
            this.on('add',this.checkSize);
        },
        checkSize:function(){
            if (this.length > this.maxSize) {
                this.models = this.models.slice(-this.maxSize)
            }
        },
        display:function(){
            this.each(function(cmd){
                console.log(cmd.get('command')+cmd.get('element'));
            });
        },
        lastElementIdBase:function(){
            var last;
            if(last=this.last()){
                if(last=ve.getElementById(last.get('element'))){
                    return last.get('id_base');
                }
            }
            return false;
        }

    });
    ve.clipboard=new Clipboard();
    ve.command=new Command();
})(ve,jQuery);