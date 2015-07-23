;var ve=ve||{};
(function(ve,$) {
    ve.TopBar = Backbone.View.extend({
        events: {
            //'click #ve-element-form .save':'updateElement',
            'click #update-post': 'savePost',
            'click #publish-post': 'publishPost',
            'click .elements-filter [data-filter]':'filterElements',
            'change #screensize': 'setScreenSize'
        },
        initialize: function () {
            var size = ve.getPostSetting('screen_size') || (1170 + 82);
            size = parseInt(size) - 82;
            this.$('#screensize').val(size);
            var _this = this;
            $.Shortcuts.add({
                type: 'hold',
                mask: 'Up',
                enableInInput: true,
                handler: function (e) {
                    if ($(e.target).is('#screensize')) {
                        _this.screen_size().up();
                    }
                }
                //list:"topbar"

            }).add({
                type: 'hold',
                mask: 'Down',
                enableInInput: true,
                handler: function (e) {
                    if ($(e.target).is('#screensize')) {
                        _this.screen_size().down();
                    }
                }
                //list:"topbar"

            });

        },
        screen_size: function () {
            var that = this;
            return {
                up: function () {
                    var current_size = that.$('#screensize').val();
                    if (current_size < 1170) {
                        current_size++;
                        that.$('#screensize').val(current_size);
                        that.setScreenSize(current_size);
                    }
                },
                down: function () {
                    var current_size = that.$('#screensize').val();
                    if (current_size > 360) {
                        current_size--;
                        that.$('#screensize').val(current_size);
                        that.setScreenSize(current_size);
                    }
                }
            }
        },
        filterElements:function(e){
            e.preventDefault();
            var filter=$(e.currentTarget).data('filter');
            ve.panel.filterElements(filter);
        },
        savePost: function (e) {
            ve.the_editor.save();
        },
        publishPost: function (e) {
            ve.the_editor.save('publish');
        },
        setScreenSize: function () {
            new_size = parseInt(this.$('#screensize').val()) + 82; //for padding
            ve.frame_view.window.ve_iframe.resizeTo(new_size);
        },
        onScreenResize: function (event, ui) {
            this.$el.find('#screensize').val(parseInt(ui.size.width) - 82);
        },

        getScreenWidth: function () {
            size = this.$el.find('#screensize').val() || 1170;
            size = parseInt(size) + 82;
            return size;
        },
        height: function () {
            return this.$el.height();
        }


    });
})(ve,jQuery);