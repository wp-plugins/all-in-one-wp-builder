//alert('css editor');

(function($){
    var VeCssEditor = Backbone.View.extend({
        events: {
        },
        initialize: function() {

        },

        render: function(value) {

        }

    });

    $('[data-css-editor=true]').each(function(){
        var $editor = $(this);
        $editor.data('cssEditor', new VeCssEditor({el: $editor}).render());
    });


})(jQuery);
