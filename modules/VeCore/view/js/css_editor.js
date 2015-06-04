//alert('css editor');

(function($){
    var preloader_url = ajaxurl.replace(/admin\-ajax\.php/, 'images/wpspin_light.gif'),
        template_options = {
            evaluate:    /<#([\s\S]+?)#>/g,
            interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
            escape:      /\{\{([^\}]+?)\}\}(?!\})/g
        };
    wp.media.controller.VeCssSingleImage = wp.media.controller.FeaturedImage.extend({
        defaults:_.defaults({
            id:'ve_single-image',
            filterable:'uploaded',
            multiple:false,
            toolbar:'ve_single-image',
            title:'Select Background',
            priority:60,
            syncSelection:false
        }, wp.media.controller.Library.prototype.defaults),
        setCssEditor: function(view) {
            if(view) this._css_editor = view;
            return this;
        },
        updateSelection:function () {
            var selection = this.get('selection'),
                id = this._css_editor.getBackgroundImage(),
                attachment;
            if (id) {
                attachment = wp.media.model.Attachment.get(id);
                attachment.fetch();
            }
            console.log(attachment);
            selection.reset(attachment ? [ attachment ] : []);
        }
    });
    var VeCssEditor = Backbone.View.extend({
        attrs: {},
        layouts: ['margin', 'border-width', 'padding'],
        positions: ['top', 'right', 'bottom', 'left'],
        $field: false,
        simplify: false,
        $simplify: false,
        events: {
            //'click .icon-remove': 'removeImage',
            //'click .ve_add-image': 'addBackgroundImage',
            'change .ve_simplify': 'changeSimplify'
            // 'change [data-attribute]': 'attributeChanged'
        },
        initialize: function() {
            // _.bindAll(wp.media.vc_css_editor, 'open');

            _.bindAll(this, 'setSimplify')
        },

        render: function(value) {
            this.attrs = {};
            this.$simplify = this.$el.find('.vc_simplify');

            // wp.media.vc_css_editor.init(this);
            return this;
        },

        addBackgroundImage: function(e) {
            e.preventDefault();
            if (this.image_media)
                return this.image_media.open('ve_editor');
            this.image_media = wp.media({
                state:'ve_single-image',
                states:[ new wp.media.controller.VeCssSingleImage().setCssEditor(this) ]
            });
            this.image_media.on('toolbar:create:ve_single-image', function (toolbar) {
                this.createSelectToolbar(toolbar, {
                    text: 'Set Background'
                });
            }, this.image_media);

            this.image_media.state('ve_single-image').on('select', this.setBgImage);
            this.image_media.open('ve_editor');
        },
        setBgImage: function() {
            var selection = this.get('selection').single();
            //console.log(selection.attributes);
            selection && this._css_editor.$el.find('.ve_background-image .ve_image').html(
                _.template($('#ve_css-editor-image-block').html(),
                    selection.attributes, _.extend({variable: 'img'},
                        template_options)));
        },
        getBackgroundImage: function() {
            return this.$el.find('.ve_ce-image').data('image-id');
        },
        changeSimplify: function(e) {
            var f = _.debounce(this.setSimplify, 100);
            f && f();
        },
        setSimplify: function() {
            this.simplifiedMode(this.$simplify.is(':checked'));

        },
        simplifiedMode: function(enable) {
            if(enable) {
                this.simplify = true;
                this.$el.addClass('ve_simplified');
            } else {
                this.simplify = false;
                this.$el.removeClass('ve_simplified');
                _.each(this.layouts, function(attr){
                    if(attr === 'border-width') attr = 'border';
                    var $control = $('[data-attribute=' + attr +'].ve_top');
                    this.$el.find('[data-attribute=' + attr +']:not(.ve_top)').val($control.val());
                }, this);
            }
        },
        removeImage: function(e) {
            var $control = $(e.currentTarget);
            e.preventDefault();
            $control.parent().remove();
        }

    });

    $('[data-css-editor=true]').each(function(){
        var $editor = $(this);
        $editor.data('cssEditor', new VeCssEditor({el: $editor}).render());
    });


})(jQuery);
