/**
 * Created by Alt on 3/16/2015.
 */
;var ve=ve||{};
(function(ve,$){

    ve.ElementFormView=Backbone.View.extend({//el=.ve-form-wrapper
        events:{
            'click #ve-element-form .cancel':'hide',
            'submit #ve-element-form':'updateElement'
        },
        formData:null,
        lastTriggerSave:0,
        initialize:function(){
            this.$form=this.$('#ve-element-form');
            this.$formContent=this.$form.find('.form-content');
            var _this=this;
            setInterval(function(){_this.checkFormDataChanged()},500);
            ve.on('element_form_changed',function(params){
                _this.updatePreview(params);
            });
            this.setDraggable();
        },
        updatePreview:function(params){
            var preview=this.$formContent.find('.ve_element_preview');
            if(preview.length) {
                var element = ve.getElementById(this.$form.data('element'));
                if (!element) {
                    return false;
                }
                element = element.clone();
                element.set('params', params, {silent: true});
                var shortcode = (element.toString());
                ve.ajax({
                    action: 've_get_element', elements: [
                        {id: element.get('id'), shortcode: shortcode, id_base: element.get('id_base')}
                    ]
                })
                    .done(function (html) {
                        preview.html(html);
                    });
            }
        },
        render:function(){
            this.setTabs();
        },
        hide:function(){
            ve.do_action('element_form_unload');
            this.$form.removeClass('content-loaded');
            this.$formContent.html('');
            this.$el.removeClass('show');
            ve.command.set({command:'',element:''},{silent:true});
        },
        updateElement:function(e){
            e.preventDefault();
            var element=ve.getElementById(this.$form.data('element'));
            var formData=this.$form.serializeArray();
            var params=this._sanitizeFormData(formData);
            params=ve.apply_filters('update_element',params);
            ve.command.set({command:'update',element:element,params:params});
            return false;
        },
        _sanitizeFormData:function(formData){
            var params={};
            _.each(formData,function(data){
                //var tokens = data.name.match(/\([^()]*\)/g);
                //var field_name = tokens.pop();
                var field_name = data.name.match(/[^[\]]+(?=])/g);
                if(field_name) {
                    field_name = field_name.pop();
                }else{
                    field_name=data.name;
                }
                if(field_name){
                    params[field_name]=data.value;
                }
            });
            return params;
        },
        checkFormDataChanged:function(){
            var formData=this.$form.serializeArray(),
                time = ( new Date() ).getTime(),
                params=this._sanitizeFormData(formData),
                editor = typeof tinymce !== 'undefined' && tinymce.get(ve.activeEditor);
            if(!this.formData){
                this.formData=params;
            }

            // Don't run editor.save() more often than every 3 sec.
            // It is resource intensive and might slow down typing in long posts on slow devices.
            if ( editor && ! editor.isHidden() && time - 3000 > this.lastTriggerSave ) {
                editor.save();
                this.lastTriggerSave = time;
            }

            if(params&&JSON.stringify(this.formData)!=JSON.stringify(params)){
                ve.do_action('element_form_changed',params);
                this.formData=params;
            }
        },
        setTitle:function(t){
            this.$('.form-title').html(t);
        },
        setDraggable:function(){
            this.$el.draggable({iframeFix:true});
        },
        setTabs:function(){
            this.$('.element-form-tabs').tabs();
        }

    });
    ve.PanelView=Backbone.View.extend({

        initialize:function(){
            this.$formWrapper=$('#ve-element-form-wrapper',this.$el);
            this.formView=new ve.ElementFormView({el:this.$formWrapper});
            this.$form=$('#ve-element-form',this.$formWrapper);
            this.$formContent=this.$form.find('.form-content');
            this.$elementsList=this.$('.element-list-item');
            this.$elements=this.$elementsList.find('.ve-element');
            this.$element_column=this.$elements.filter('.ve_element-ve_col');
            this.$elements=this.$elements.filter(function(){
                return !$(this).hasClass('ve_element-ve_col');
            });

            this.$panel_items=this.$('.panel-items');
            this.$closeform=$('.close-element-form');
            this.$closeform.on('click',function(e){
                e.preventDefault();
                ve.panel.hideForm();
            });
            ve.on('element_form_unload',function(){
                this.removeEditor();
                this.$closeform.hide();
            },this);
            ve.on('element_form_loaded',function(e){
                this.$closeform.show();
                this.setupEditor(e);
                if(wp&&wp.media&&wp.media.ve_editor){
                    wp.media.ve_editor.setupButton(this.$formContent);
                }
            },this);

            this.elementDraggable();
            this.$el.find('.tooltip').tooltipster({
                position: 'right'
            });
            ve.on('ve_loaded',function(){
                var height=$(window).height()-ve.topbar.height();
                this.$('.element-list ul').slimscroll({width:'100%',height:height});
            },this);

        },


        hide:function(){
            this.$panel_items.find('.panel-item').removeClass('show');
            this.$panel_items.find('ul.panel-items-control li a').removeClass('active');
            this.hideForm();
        },

        savePost:function(){
            ve.the_editor.save();
        },
        loadElementForm:function(element_id){
            var element=ve.getElementById(element_id);
            var shortcode=element.toString();
            var _this=this;

            this.$formWrapper.addClass('loading-content');
            this.$form.removeClass('content-loaded');
            this.$formWrapper.addClass('show');

            ve.do_action('element_form_unload');
            this.formView.setTitle(element.setting('title') + " options");
            _this.$formContent.attr('class',"form-content " + element.get('id_base'));
            ve.ajax({action:'ve_get_form',element:element.get('id_base'),shortcode:shortcode}).done(function(data){
                _this.$formContent.html(data);
                _this.$form.attr('data-element',element_id).data('element',element_id);
                if( _this.$formContent.find(':input').length>0) {
                    _this.$form.addClass('content-loaded');
                }
                _this.$formWrapper.removeClass('loading-content');
                _this.formView.render();
                ve.do_action('element_form_loaded',element);
            });
        },
        hideForm:function(){
            this.formView.hide();
        },
        filterElements:function(filter){
            if(filter=='all'){
                this.$elementsList.show();
                return;
            }
            this.$elementsList.hide();
            this.$elementsList.filter('[data-group='+filter+']').show();
        },

        elementDraggable:function(){
            this.$elements.draggable({
                revert: true,
                scope: "ve-add-element",
                helper: "clone",
                iframeFix:true,

                drag:function(event,ui){
                    var instance;
                    try {
                        instance = $(this).draggable('instance');
                    }catch(e){}
                    if(!instance){
                        instance=$(this).data('uiDraggable');
                    }
                    instance.positionAbs.top+=ve.frame_view.scrollTop()-60;
                }

            });
            this.$element_column.draggable({
                revert: true,
                scope: "ve-add-column",
                helper: "clone",
                iframeFix:true,

                drag:function(event,ui){
                    var instance;
                    try {
                        instance = $(this).draggable('instance');
                    }catch(e){}
                    if(!instance){
                        instance=$(this).data('uiDraggable');
                    }
                    instance.positionAbs.top+=ve.frame_view.scrollTop()-60;
                }

            });
        },
        refresh:function(){
            this.elementDraggable();
            ve.content_view.droppable();
        },
        showMessage:function(message,timeout,target) {
            var _this=this;
            timeout=timeout||5500;
            target=target||this.$el;
            if(this.message_timeout) {
                target.find('.ve_message').remove();
                window.clearTimeout(this.message_timeout);
            }
            var $message = $('<div class="ve_message success"><i class="fa fa-check-circle"></i> ' + message + '</div>').prependTo(target);
            $message.fadeIn(500);
            this.message_timeout = window.setTimeout(function(){
                $message.fadeOut(500, function(){$(this).remove();});
                _this.message_timeout = false;
            }, timeout);
        },

        removeEditor:function(){
            if(!_.isUndefined(window.tinyMCE)) {
                $('.ve-html-editor', this.$el).each(function () {
                    var id = $(this).attr('id');
                    window.tinyMCE.execCommand('mceRemoveEditor', true, id);
                });
                ve.activeEditor='';
            }
        },
        setupEditor:function(e){
            var _this=this;
            $('.ve-html-editor').each(function(){
                _this._setupEditor(this)
            });
        },
        _setupEditor:function($element){
            $element=$($element);
            /*
             Simple version without all this buttons from Wordpress
             tinyMCE.init({
             mode : "textareas",
             theme: 'advanced',
             editor_selector: $element.attr('name') + '_tinymce'
             });
             */

            var qt, textfield_id = $element.attr("id");

            if(!textfield_id){
                return false;
            }
            // Init Quicktag
            if(tinyMCEPreInit&&_.isUndefined(tinyMCEPreInit.qtInit[textfield_id])) {
                window.tinyMCEPreInit.qtInit[textfield_id] = _.extend({}, window.tinyMCEPreInit.qtInit[wpActiveEditor], {id: textfield_id})
            }
            // Init tinymce
            if(window.tinyMCEPreInit && window.tinyMCEPreInit.mceInit[wpActiveEditor]) {
                window.tinyMCEPreInit.mceInit[textfield_id] = _.extend({}, window.tinyMCEPreInit.mceInit[wpActiveEditor], {
                    resize: 'vertical',
                    height: 200,
                    id: textfield_id,
                    setup: function (ed) {
                        if (typeof(ed.on) != 'undefined') {
                            ed.on('init', function (ed) {
                                ed.target.focus();

                            });
                        } else {
                            ed.onInit.add(function (ed) {
                                ed.focus();

                            });
                        }
                    }
                });
                if(window.tinyMCEPreInit.mceInit[textfield_id].plugins) {
                    window.tinyMCEPreInit.mceInit[textfield_id].plugins = window.tinyMCEPreInit.mceInit[textfield_id].plugins.replace(/,?wpfullscreen/, '');
                }
            }

            qt = quicktags( window.tinyMCEPreInit.qtInit[textfield_id] );
            QTags._buttonsInit();
            if(window.tinymce) {
                window.switchEditors && window.switchEditors.go(textfield_id, 'tmce');
                if(tinymce.majorVersion === "4") tinymce.execCommand( 'mceAddEditor', true, textfield_id );
                ve.activeEditor=textfield_id;
            }
        }
    });

})(ve,jQuery);