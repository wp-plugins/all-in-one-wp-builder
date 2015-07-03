(function($,_){
    var media = wp.media,
        Attachment = media.model.Attachment,
        Attachments = media.model.Attachments,
        Query = media.model.Query,
        l10n = i18nLocale,
        workflows = {};
    media.controller.VeGallery = media.controller.FeaturedImage.extend({
        defaults:_.defaults({
            id:'ve_gallery',
            title:l10n.add_images,
            toolbar:'main-insert',
            filterable:'uploaded',
            library:media.query({type:'image'}),
            multiple:true,
            editable:true,
            priority:60,
            syncSelection:false
        }, media.controller.Library.prototype.defaults),
        updateSelection:function () {
            var selection = this.get('selection'),
                ids = media.ve_editor.getData(),
                attachments;
            if ('' !== ids && -1 !== ids) {
                attachments = _.map(ids.split(/,/), function (id) {
                    return Attachment.get(id);
                });
            }
            selection.reset(attachments);
        }
    });
    media.view.MediaFrame.VeGallery = media.view.MediaFrame.Post.extend({
        createStates:function () {
            var options = {
                multiple:this.options.multiple,
                title:this.options.multiple?l10n.add_images:l10n.add_image
            };
            if(this.options.title){
                options.title=this.options.title;
            }
            this.states.add([
                // Main states.
                new media.controller.VeGallery(options)
            ]);
        },
        // Removing let menu from manager
        bindHandlers:function () {
            media.view.MediaFrame.Select.prototype.bindHandlers.apply(this, arguments);
            this.on('toolbar:create:main-insert', this.createToolbar, this);

            var handlers = {
                content:{
                    'embed':'embedContent',
                    'edit-selection':'editSelectionContent'
                },
                toolbar:{
                    'main-insert':'mainInsertToolbar'
                }
            };

            _.each(handlers, function (regionHandlers, region) {
                _.each(regionHandlers, function (callback, handler) {
                    this.on(region + ':render:' + handler, this[ callback ], this);
                }, this);
            }, this);
        },
        // Changing main button title
        mainInsertToolbar:function (view) {
            var controller = this;
            var options=this.options;
            var btn_text=options.multiple?l10n.add_images:l10n.add_image;
            if(options.btn_text){
                btn_text=options.btn_text;
            }
            this.selectionStatusToolbar(view);

            view.set('insert', {
                style:'primary',
                priority:80,
                text:btn_text,
                requires:{ selection:true },

                click:function () {
                    var state = controller.state(),
                        selection = state.get('selection');

                    controller.close();
                    state.trigger('insert', selection).reset();
                }
            });
        }
    });
    media.ve_editor ={};
    _.extend(media.ve_editor, {
        $ve_editor_element:null,
        setupButton:function($wrapper){

            $wrapper.find('.ve-media-add-images-btn').each(function(){
                var $button=$(this),
                    $block=$button.closest('.ve_input_block');
                var img_ids = [];
                $block.find('.added img').each(function () {
                    img_ids.push($(this).attr("rel"));
                });

                media.ve_editor.setButtonClass($button,img_ids&&img_ids.length,img_ids);
            });
        },
        setButtonClass:function($button,hasData,items){
            var multiple=!!$button.data('multiple');
            var className=multiple?'multi-':'';
            if(hasData){
                className+='has-data';
            }
            $button.removeClass('has-data multi-has-data');
            $button.addClass(className);
        },
        getData:function () {
            var $button = media.ve_editor.$ve_editor_element,
                $block = $button.closest('.ve_input_block'),
                $hidden_ids = $block.find('.ve-media-selected-images-ids');
            return $hidden_ids.val();
        },
        insert:function (images) {
            var $button = media.ve_editor.$ve_editor_element,
                $block = $button.closest('.ve_input_block'),
                $hidden_ids = $block.find('.ve-media-selected-images-ids'),
                $img_ul = $block.find('.ve-media-selected-images-list'),
                $thumbnails_string = '';

            _.each(images, function (image) {
                $thumbnails_string += _.template($('#ve_template_block-image').html(), image);
            });
            this.setButtonClass($button,images&&images.length,images);
            $hidden_ids.val(_.map(images,function (image) {
                return image.id;
            }).join(',')).trigger('change');
            $img_ul.html($thumbnails_string);

        },
        id:function(id){
            return id;
        },
        get: function( id ) {
            id = this.id( id );
            return workflows[ id ];
        },
        open:function (id,options) {
            var workflow, editor;

            id = this.id(id);

            workflow = this.get(id);

            // Redo workflow if state has changed
            if (!workflow || ( workflow.options && options.state !== workflow.options.state ))
                workflow = this.add(id,options);

            return workflow.open();
        },
        add:function (id, options) {
            var workflow = this.get(id);
            if (workflow)
                return workflow;

            workflow = workflows[ id ] = new media.view.MediaFrame.VeGallery(_.defaults(options || {}, {
                state:'ve_gallery',
                multiple:false
            }));
            workflow.on('insert', function (selection) {
                var state = workflow.state(),
                    data = [];

                selection = selection || state.get('selection');
                if (!selection)
                    return;

                this.insert(_.map(selection.models, function (model) {
                    return model.attributes;
                }),options);
            }, this);
            return workflow;
        },
        init:function () {
            var body=$('body');
            body.on('click.veGalleryWidget', '.ve-media-add-images-btn', function (event) {
                var $this = $(this),
                    editor = 've',
                    suffix='',
                    multiple=!!$this.data('multiple');
                var options={
                    multiple:multiple,
                    title:$this.data('title'),
                    btn_text:$this.data('button')
                };
                if(options.title||options.btn_text){
                    suffix=ve.php.md5(options.title+options.btn_text);
                }
                if(multiple){
                    editor+='-multiple';
                }
                editor+=suffix;
                media.ve_editor.$ve_editor_element = $this;
                event.preventDefault();
                $this.blur();
                media.ve_editor.open(editor,options);
            });
            body.on('click.removeImage', '.ve-media-selected-images-list a.icon-remove', function(e){
                e.preventDefault();
                var $block = $(this).closest('.ve_input_block'),
                    $button=$block.find('.ve-media-add-images-btn');
                $(this).parent().remove();
                var img_ids = [];
                $block.find('.added img').each(function () {
                    img_ids.push($(this).attr("rel"));
                });

                media.ve_editor.setButtonClass($button,img_ids&&img_ids.length,img_ids);
                $block.find('.ve-media-selected-images-ids').val(img_ids.join(',')).trigger('change');
            });
        }
    });
    _.bindAll(media.ve_editor, 'open');
    $(document).ready(function () {
        media.ve_editor.init();
    });
})(jQuery,_);