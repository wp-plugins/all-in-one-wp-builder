/* global tinymce */

tinymce.PluginManager.add( 've_shortcodes', function( editor ) {
    editor.addCommand( 'Ve_Popup', function() {
        window.wpLink && window.wpLink.open( editor.id );
    });

    editor.addCommand( 'Ve_Widget', function() {
        window.wpLink && window.wpLink.open( editor.id );
    });

    editor.addButton( 've_popup', {
        icon: 'wp_page',
        tooltip: 'Insert Popup',
        cmd: 'Ve_Popup'
    });

    editor.addButton( 've_widget', {
        icon: 'wp_page',
        tooltip: 'Insert Widget',
        cmd: 'Ve_Widget'
    });



});

var Ve_Mce_Dialog,Ve_Popup;
var Ve_Widget;
(function($){
    var editor, inputs = {};
    Ve_Mce_Dialog={
        init:function(wrap){
            var _this=this;
            inputs.wrap=$(wrap);
            inputs.form=inputs.wrap.find('form');
            inputs.form.on('submit',_this.update);
        },
        update:function(e){
            e.preventDefault();

            return false;
        },
        open: function( editorId ) {
            var ed;

            $( document.body ).addClass( 'modal-open' );

            if ( editorId ) {
                window.wpActiveEditor = editorId;
            }

            if ( ! window.wpActiveEditor ) {
                return;
            }

            this.textarea = $( '#' + window.wpActiveEditor ).get( 0 );

            if ( typeof tinymce !== 'undefined' ) {
                ed = tinymce.get( wpActiveEditor );

                if ( ed && ! ed.isHidden() ) {
                    editor = ed;
                } else {
                    editor = null;
                }

                if ( editor && tinymce.isIE ) {
                    editor.windowManager.bookmark = editor.selection.getBookmark();
                }
            }


            inputs.wrap.show();
            //inputs.backdrop.show();


            $( document ).trigger( 've-dialog-open', inputs.wrap );
        }


    };
    Ve_Widget={

    };
    $( document ).ready( Ve_Popup.init );
})(jQuery);
