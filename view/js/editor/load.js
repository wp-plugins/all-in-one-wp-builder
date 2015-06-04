if(_.isUndefined(ve)) var ve = {};
(function($){
    ve.load=function(){
        ve.loaded = true;
        ve.setTemplateOptions();
        ve.content_loaded=false;
        ve.controller.init();

        // Get current content data
        ve.post=window.ve_post;
        ve.post_title=ve.post.post_title;
        ve.post_id=ve.post.ID;
        ve.post_elements = window.ve_post_elements;
        ve.all_elements=window.ve_elements;
        ve.settings=window.ve_settings||{};
        ve.post_settings=window.ve_post_settings||{};
        ve.$frame_wrapper=$('#ve_inline-frame-wrapper');
        ve.$frame = $('#ve_inline-frame');
        ve.frame_window=ve.$frame.get(0).contentWindow;
        ve.$frame_document=$(ve.frame_window.document);
        ve.$page=$('#ve-editor-holder',ve.$frame_document).parent();
        $.Shortcuts.start(window).start(ve.frame_window);

        ve.setFrameSize('100%');
        //Main view target to body
        ve.view=new ve.EditorView({el:'body'});
        //Panel view target to panel
        ve.panel=new ve.PanelView({el:'#ve-panel'});
        //top bar view
        ve.topbar=new ve.TopBar({el:'#ve-topbar'});
        //form view
        ve.formview=new ve.FormView({el:'body'});
        //Iframe view target to iframe body
        ve.frame_view= new ve.FrameView({el:ve.$frame_document.find('body').get(0)});
        //Content View target to post content
        ve.content_view=new ve.PostContentView({el:ve.$page});
        ve.view.render();
        ve.the_editor.buildFromContent();
        ve.panel.refresh();


    };
})(window.jQuery);