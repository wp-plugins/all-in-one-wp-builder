/**
 * Created by alt on 3/8/2015.
 */
var ve=ve||{};
(function(ve,$){
    ve.EditorView=Backbone.View.extend({
        mode: 'view',
        current_size: '100%',
        events:{
            "click #toggle-mode":"toggleMode",
            "click [data-cmd]":"onCmd"
        },

        initialize: function() {
            _.bindAll(this, 'saveRowOrder', 'saveElementOrder', 'saveColumnOrder', 'resizeWindow');

        },
        render: function() {
            this.setMode('compose');
            ve.$page.addClass('ve_post_content ve-post-content');
            return this;
        },
        onCmd:function(e){
            if(e){
                e.stopPropagation();
                e.preventDefault();
                var cmd=$(e.currentTarget).data('cmd');
                ve.command.set({command: cmd, element: e.currentTarget, time:ve.time()});
            }
        },
        cancel: function(e) {
            _.isObject(e) && e.preventDefault();
            window.location.href = $(e.currentTarget).data('url');
        },
        save: function(e) {
            _.isObject(e) && e.preventDefault();
            ve.the_editor.save($(e.currentTarget).data('changeStatus'));
        },

        resizeWindow: function() {
            ve.setFrameSize(this.current_size);
        },
        switchMode: function(e) {
            var $control = $(e.currentTarget);
            e && e.preventDefault();
            this.setMode($control.data('mode'));
            $control.siblings('.ve_active').removeClass('ve_active');
            $control.addClass('ve_active');
        },
        toggleMode: function(e) {
            var $control = $(e.currentTarget);
            e && e.preventDefault();
            if(this.mode === 'compose') {
                $control.addClass('ve_off').text('Grid On');
                this.setMode('view');
            } else {
                $control.removeClass('ve_off').text('Grid Off');
                this.setMode('compose');
            }
        },
        setMode: function(mode) {
            this.$el.removeClass(this.mode + '-mode');
            ve.$frame_body.removeClass(this.mode + '-mode');
            this.mode = mode;
            this.$el.addClass(this.mode + '-mode');
            ve.$frame_body.addClass(this.mode + '-mode');
        },


        setFrameSize: function() {
            ve.setFrameSize();
        },

        saveRowOrder: function() {
            var $rows = ve.$page.find('> [data-id-base=ve_row]');
            $rows.each(function (key, value) {
                var $this = $(this);
                ve.getElementById($this.data('element-id')).save({'order':key}, {silent: true});
            });
        },
        saveColumnOrder: function(event, ui) {
            var row = ui.item.parent();
            row.find('> [data-element-id]').each(function(){
                var $element = $(this),
                    index = $element.index();
                ve.getElementById($element.data('element-id')).save({order: index},{silent: true});
            });
        },
        saveElementOrder: function(event, ui) {
            if(_.isNull(ui.sender)) {
                var $column = ui.item.parent(),
                    $elements = $column.find('> [data-element-id]');
                $column.find('> [data-element-id]').each(function(key, value){
                    var $element = $(this),
                        model, prev_parent, current_parent, prepend = false;

                        model = ve.elements.get($element.data('element-id'));
                        prev_parent = model.get('parent_id');
                        current_parent = $column.parents('.ve_element[data-id-base]:first').data('element-id');
                        model.save({order: key, parent_id: current_parent}, {silent: true});

                        if(prev_parent!==current_parent) {

                            ve.do_action('element_moved',model.get('id'),prev_parent,current_parent)
                        }


                });
            }
        }

    });
    ve.FrameView=Backbone.View.extend({
        events: {
            'click .ve-post_title':'showPostSetting'

        },
        showPostSetting:function(e){
            ve.log('show post setting');
            ve.log(e);
        },
        setTitle: function(title) {

        },
        initialize: function() {
            ve.$frame_body=this.$el;
            this.window=ve.frame_window;
        },

        setSortable: function() {
            this.window.ve_iframe.setSortable();
        },
        render: function() {
            ve.$title = this.$el.find('h1:contains(' + ve.post_title + ')');
            ve.$title.addClass('ve-post_title');
            this.window.ve_iframe.setSortable();
            this.window.ve_iframe.setResizeAble();
            return this;
        },

        addElement: function(e) {

        },

        scrollTo: function(model) {

        },
        addScripts: function(e){
            this.window.ve_iframe.addScripts(e);
        },
        addElementCustomStyle:function(css){
            this.window.ve_iframe.addElementCustomStyle(css);
        },
        loadScripts:function(){
            this.window.ve_iframe.loadScripts();
        },
        doScripts:function(code){
            this.window.ve_iframe.doScripts(code);
        },
        scrollTop:function(){
            return $(this.window).scrollTop();
        },
        addInlineScript: function(script) {

        },
        addInlineScriptBody: function(script) {

        }
    });
    ve.PostContentView=Backbone.View.extend({
        initialize:function(){
            this.$el.addClass('ve_post_content');
        },
        placeElement: function($view, activity) {
            var element = ve.elements.get($view.data('element-id'));
            if(element && element.get('place_after_id')) {
                $view.insertAfter(this.$el.find('[data-element-id=' + element.get('place_after_id') + ']'));
                element.unset('place_after_id');
            } else if(_.isString(activity) && activity === 'prepend') {
                $view.prependTo(this.$el);
            } else {
                $view.insertBefore(this.$el.find('#ve_no_content_helper'));
            }
        },
        clearSelected:function(){
            this.$el.find('.ve_selected').removeClass('ve_selected');
            this.$el.find('.ve_selected-parent').removeClass('ve_selected-parent');
        },
        clearCutting:function(){
            this.$el.find('.ve_cutting').removeClass('ve_cutting');
        },
        /**
         * Get single selected element, if multiple return false
         */
        getSelected:function(){
            var selected=this.$el.find('.ve_selected');
            if(selected.length==1){
                return selected.data('element-id');
            }
            return false;
        },
        getSelectedElements:function(){
            var selected=[];
            var $selected=this.$el.find('.ve_selected');
            $selected.each(function () {
                selected.push($(this).data('element-id'));
            });
            return selected;
        },
        droppable:function(){
            this.$('.ve_element-ve_col,#ve_no_content_helper .ve_buttons').droppable({
                activeClass: "ui-state-default",
                hoverClass: "ui-state-hover",
                scope: "ve-add-element",
                iframeFix: true,
                drop: function( event, ui ) {
                    $( this ).find( ".placeholder" ).remove();
                    event.stopPropagation();
                    var to=$(this).closest('[data-element-id]').data('element-id');
                    if($(this).is('.ve_buttons')) {
                        to='';
                    }
                    var $element=ui.draggable.closest('[data-id-base]');
                    ve.command.set({
                        command: 'add',
                        'from': $element.data('id-base'),
                        'to': to,
                        'params':$element.data('params'),
                        rand:ve_guid()
                    });

                }
            });
            this.columnHolderDroppable();
        },
        columnHolderDroppable:function(){
            this.$('.ve_col-placeholder').droppable({
                activeClass: "ui-state-default",
                hoverClass: "ui-state-hover",
                scope: "ve-add-column",
                iframeFix: true,
                drop: function( event, ui ) {
                    $( this ).find( ".placeholder" ).remove();
                    event.stopPropagation();
                    var to=$(this).closest('[data-element-id]').data('element-id');
                    ve.command.set({
                        command: 'add',
                        from: ui.draggable.closest('[data-id-base]').data('id-base'),
                        to: to,
                        width: $(this).data('columns')+'/12',
                        rand:ve_guid()
                    });

                }
            });
        },
        render:function(){

        }
    });


    ve.FormView=Backbone.View.extend({
        initialize:function(){
            this.initAjaxForm();
            this.formInputCondition();
            this.initSettingForms();
        },
        initSettingForms:function(){
            $('.ve-ui-tabs').tabs();
            $('.ve_color-control').wpColorPicker();
        },
        formInputCondition:function(){
            $('[data-show-if]',this.$el).each(function(){
                var $block=$(this),
                    handle_id=$block.data('show-if'),
                    handle_value=$block.data('show-value'),
                    comparator=$block.data('compare')||'==';

                if(!$block.data('bind-show-if')){
                    var handle=$('#'+handle_id);
                    if(handle.is('select')) {
                        var select_handle_function=function () {
                            var result;
                            if($.isArray(handle_value)){
                                result=(-1!==handle_value.indexOf(handle.val()));
                            }else {
                                result = handle_value == handle.val();
                            }
                            if(comparator!='=='){
                                result=!result;
                            }
                            if (result) {
                                $block.removeClass('ve_hide');
                            } else {
                                $block.addClass('ve_hide');
                            }
                        };
                        handle.on('change', select_handle_function);
                        select_handle_function();
                    }
                    //console.log(handle);
                    if(handle.is('input[type=checkbox]')){
                        var checkbox_handle_function=function () {
                            var result;
                            result=handle.is(handle_value);
                            if(comparator!='=='){
                                result=!result;
                            }
                            if (result) {
                                $block.removeClass('ve_hide');
                            } else {
                                $block.addClass('ve_hide');
                            }
                        };
                        handle.on('click', checkbox_handle_function);
                        checkbox_handle_function();
                    }
                    $block.data('bind-show-if',true);
                }

            })
        },
        initAjaxForm:function(){
            var _this=this;
            $('.ve-ajax-form').on('submit',function(e){
                e.preventDefault();
                var form=$(this);
                var formData=form.serializeObject();
                if(!formData||!formData.action){
                    return false;
                }
                formData=ve.apply_filters('ajax_form_data',formData,formData.action);
                if(!formData||!formData.action){
                    return false;
                }

                ve.ajax(formData, "json").done(function (result) {
                    ve.do_action('ajax_form_done', formData.action, formData, result, form);
                    ve.do_action('ajax_form_done_' + formData.action, formData, result, form);
                    if (form.attr('data-update-values')) {
                        _this.updateFormValues(form, result);
                    }
                });

                return false;
            }).on('click','[data-submit]',function(e){
                e.preventDefault();
                var submit=$(this);
                var form=submit.closest('form');
                var formData=form.serializeObject();
                var extraData=submit.data('submit');
                var overwrite=submit.data('overwrite');
                if(extraData){
                    if(overwrite){
                        formData=extraData;
                    }else {
                        _.extend(formData, extraData);
                    }
                }
                if(!formData||!formData.action){
                    return false;
                }
                formData=ve.apply_filters('ajax_form_data',formData,formData.action);
                if(!formData||!formData.action){
                    return false;
                }
                form.data('this',this);
                ve.ajax(formData, "json").done(function (result) {
                    ve.do_action('ajax_form_done', formData.action, formData, result, form);
                    ve.do_action('ajax_form_done_' + formData.action, formData, result, form);
                    if (form.attr('data-update-values')) {
                        _this.updateFormValues(form, result);
                    }
                });

                return false;
            });
            ve.on('ajax_form_done',function(action,data,res,form){
                var message=res.message||form.data('message')||data.message||'Updated';
                _this.showMessage(message,3000,form);
            },this);
        },
        showMessage:function(message,timeout,target) {
            ve.panel.showMessage(message,timeout,target);
        },
        updateFormValues:function(form,value){
            if(typeof value=='object') {
                $(form).find('input').each(function () {
                    var input = $(this), input_name = input.attr('name');
                    if (typeof value[input_name]!="undefined") {
                        input.val(value[input_name]);
                        input.attr('value',value[input_name]);
                    }
                });
            }
        }
    });

})(ve,jQuery);