var ve_iframe = {
    scripts_to_wait: 0,
    time_to_call: false,
    ajax: false,
    activities_list: [],
    scripts_to_load: false,
    loaded_script: {},
    loaded_styles: {},
    inline_scripts: [],
    inline_scripts_body: []
};

var ve=ve||parent.ve;
var md5=md5||ve.php.md5;
(function(iframe,$) {

    iframe.init=function(){
        var excludeScripts=['jquery-core','jquery-migrate'],ls_rc,i;
        for(i in excludeScripts) {
            ls_rc = 'load-script:' + excludeScripts[i];
            if(!iframe.loaded_script[window.md5(ls_rc)]) {
                iframe.loaded_script[window.md5(ls_rc)] = ls_rc;
            }
        }
    };
    iframe.startSorting = function() {
        $('html').addClass('ve_sorting');
    };
    iframe.stopSorting = function() {
        $('html').removeClass('ve_sorting')
    };
    iframe.initDroppable = function() {
        $('html').addClass('ve_dragging');
        $drop=$('.ve_element-ve_col');
        $drop.addClass('ui-state-default');
        $drop.bind('mouseover.vcDraggable', function(){
            $(this).addClass('ui-state-hover');
        }).bind('mouseout.vcDraggable', function(){
            $(this).removeClass('ui-state-hover');
        });
    };
    iframe.killDroppable = function() {
        $('body').removeClass('ve_dragging');
        $('.ve_container-block').unbind('mouseover.vcDraggable mouseleave.vcDraggable');
    };

    iframe.addActivity = function(callback) {
        this.activities_list.push(callback);
    };
    iframe.renderPlaceholder = function(event, element) {
        var tag = $(element).data('id-base'),
            $helper = $('<div class="ve_helper ve_helper-' + tag + '"><i class="ve_element-icon'
            + ( parent.ve.getElementSetting(tag).icon_class ? ' ' + parent.ve.getElementSetting(tag).icon_class : '' )
            + '"></i> ' + parent.ve.getElementSetting(tag).name + '</div>').prependTo('body');
        return $helper;
    };
    iframe.setResizeAble = function(){
        $.widget("ui.resizable", $.ui.resizable, {
            resizeTo: function(newSize) {
                var start = new $.Event("mousedown", { pageX: 0, pageY: 0 });
                this._mouseStart(start);
                this.axis = 'se';
                var end = new $.Event("mouseup", {
                    pageX: newSize.width - this.originalSize.width,
                    pageY: newSize.height - this.originalSize.height
                });
                this._mouseDrag(end);
                this._mouseStop(end);
            }
        });
        $(".ve-editor-view").resizable({
            handles: "w",
            minWidth: 360,
            maxWidth:1170,
            resize: function( event, ui ) {
                ui.position.left=0;
                parent.ve.onScreenResize(event, ui);
            },
            stop:function(event,ui){
                ve.saveScreenSize(event,ui);
            },
            create: function( event, ui ) {
                $(this).css('width',ve.topbar.getScreenWidth());
                //ui.size.width=ve.topbar.getScreenWidth();
            }
        });

    };
    iframe.resizeTo=function(width){
        $(".ve-editor-view").resizable("resizeTo", { width: width });
    };
    iframe.setSortable = function(){

        $('[data-id-base="ve_row"]').first().parent().sortable({
            forcePlaceholderSize: false,
            items: '[data-id-base=ve_row]',
            handle: '.row-controls',
            cursor: 'move',
            cursorAt: {top: 20, left: 200},
            placeholder: "ve_placeholder-row",
            helper: this.renderPlaceholder,
            start: function(event, ui){
                ve_iframe.startSorting();
                var height=ui.item.height();
                ui.placeholder.height(height);

            },
            stop: this.stopSorting,
            tolerance: "pointer",
            update: parent.ve.view.saveRowOrder
        });

        $('.ve_element-ve_row .ve_element_container').sortable({
            forcePlaceholderSize: false,
            items: '> div',
            handle: '.col-controls',
            cursor: 'move',
            cursorAt: {top: 20, left: 200},
            placeholder: "ve_placeholder-col",
            helper: this.renderPlaceholder,
            start: function(event, ui){
                ve_iframe.startSorting();
                var id = ui.item.data('element-id'),
                    model = parent.ve.getElementById(id),
                    css_class = model.view.convertSize(model.getParam('width'));
                // ui.item.removeClass(css_class).data('removedClass', css_class);
                var height=ui.item.height();
                ui.item.appendTo(ui.item.parent().parent());
                ui.placeholder.addClass(css_class);
                ui.placeholder.width(ui.placeholder.width()-4);
                ui.placeholder.height(height);
            },
            stop: this.stopSorting,
            tolerance: "pointer",
            update: parent.ve.view.saveColumnOrder
        });

        $('.ve_element-ve_col>.ve_element_container').sortable({
            forcePlaceholderSize: true,
            helper: this.renderPlaceholder,
            distance: 3,
            scroll: true,
            scrollSensitivity: 70,
            cursor: 'move',
            cursorAt: {top: 20, left: 200},
            connectWith:'.ve_element-ve_col>.ve_element_container',
            items: '> div',
            handle: '.element-controls',
            placeholder: "ve_placeholder-element",
            start: this.startSorting,
            update: parent.ve.view.saveElementOrder,
            change: function(event, ui) {
                var height=ui.item.height();
                if(height>200){
                    height=200;
                }
                ui.placeholder.height(height);
                ui.placeholder.width(ui.placeholder.parent().width());
            },
            tolerance: "pointer",
            over:function (event, ui) {

            },
            out: function(event, ui) {
                ui.placeholder.removeClass('ve_hidden-placeholder');

            },
            stop:function (event, ui) {
                
                ve_iframe.stopSorting();
            }
        });


        
    };

    iframe.allowedLoadScript = function(src) {
        var script_url, i, scripts_string, scripts = [], scripts_to_add = [], ls_rc;
        if(src.match(/load\-scripts\.php/)) {
            scripts_string = src.match(/load%5B%5D=([^&]+)/)[1];
            if(scripts_string) scripts = scripts_string.split(',');
            for(i in scripts) {
                ls_rc = 'load-script:' + scripts[i];
                if(!iframe.loaded_script[window.md5(ls_rc)]) {
                    iframe.loaded_script[window.md5(ls_rc)] = ls_rc;
                    scripts_to_add.push(scripts[i]);
                }
            }
            return !scripts_to_add.length ? false : src.replace(/load%5B%5D=[^&]+/, 'load%5B%5D=' + scripts_to_add.join(','));
        } else if(!iframe.loaded_script[window.md5(src)]) {
            iframe.loaded_script[window.md5(src)] = src;
            return src;
        }
        return false;
    };
    iframe.addScripts = function($elements) {
        iframe.scripts_to_wait = $elements.length;
        iframe.scripts_to_load = $elements;
    };
    iframe.addElementCustomStyle=function(style){
        if(!iframe.$elementStyle){
            iframe.$elementStyle=$('#ve_element_custom_css');
            if(!iframe.$elementStyle.length){
                iframe.$elementStyle=$('<style type="text/css" id="ve_element_custom_css"></style>');
                $('head').append(iframe.$elementStyle);
            }
        }
        style&&iframe.$elementStyle.append(style);

    };
    iframe.loadScripts = function() {
        console.log('load script');
        if(!iframe.scripts_to_wait || !iframe.scripts_to_load) {
            iframe.reload();
            return;
        }

        var scripts=iframe.scripts_to_load.filter('script');
        var styles=iframe.scripts_to_load.filter(':not(script)');
        //console.log(scripts);
        if(styles.length) {
            styles.each(function () {
                var $element = $(this);
                var href = $element.attr('href');
                if(!iframe.loaded_styles[window.md5(href)]) {
                    $('<link/>', {
                        rel: 'stylesheet',
                        type: 'text/css',
                        href: href
                    }).appendTo('body');
                }
            });
        }
        if(scripts.length){
            var total=scripts.length;
            scripts.each(function(){
                var $element = $(this);
                //console.log($element);
                var src = $element.attr('src');
                src = iframe.allowedLoadScript(src);
                if(src) {
                    $.getScript(src, function() {
                        total -=1;
                        total < 1 && iframe.reload();
                    });
                } else {
                    total -=1;
                    total < 1 && iframe.reload()
                }
            });
        }
        iframe.scripts_to_wait=false;

    };
    iframe.reload=function(){
        console.log('ready');
        ve.do_action('ready',$);
    };
    iframe.doScripts=function(code){
        $.globalEval(code);
    };



    function ve_iframe_load(){
        if(parent.ve&&!parent.ve.loaded){
            parent.ve.load();
            ve_iframe_control_init();
        }else{
            setTimeout('ve_iframe_load();',200);
        }
    }
    function ve_iframe_control_init(){
        ve.row_controls=$.contextMenu({
            selector: '.row-controls .move',
            callback: function(key, options) {
                $e=options.$trigger.closest('[data-id-base]');
                parent.ve.command.set({command:key,element:$e.data('element-id')});

            },
            items: {
                "edit": {name: "Edit", icon: "edit"},
                "paste": {name: "Paste", icon: "paste",disabled:ve.controller.commandDisabled.row.paste},
                "delete": {name: "Delete", icon: "delete"},
                "sep1": "---------",
                "quit": {name: "Cancel", icon: "quit"}
            }
        });


       ve.col_controls= $.contextMenu({
            selector: '.col-controls .move',
            callback: function(key, options) {
                $e=options.$trigger.closest('[data-id-base]');
                parent.ve.command.set({command:key,element:$e.data('element-id')});

            },
            items: {
                "edit": {name: "Edit", icon: "edit"},
                "cut": {name: "Cut", icon: "cut"},
                "copy": {name: "Copy", icon: "copy"},
                "paste": {name: "Paste", icon: "paste", disabled:ve.controller.commandDisabled.col.paste},
                "delete": {name: "Delete", icon: "delete"},
                "sep1": "---------",
                "quit": {name: "Cancel", icon: "quit"}
            }
        });

        ve.element_controls=$.contextMenu({
            selector: '.element-controls .move',
            callback: function(key, options) {
                $e=options.$trigger.closest('[data-id-base]');
                parent.ve.command.set({command:key,element:$e.data('element-id')});

            },
            items: {
                "cut": {name: "Cut", icon: "cut"},
                "copy": {name: "Copy", icon: "copy"},
                "save": {name: "Save", icon: "save"},
                "delete": {name: "Delete", icon: "delete"},
                "sep1": "---------",
                "quit": {name: "Cancel", icon: "quit"}
            }
        });

        $.contextMenu({
            selector: '.ve_multiple_selected',
            callback: function(key, options) {
                parent.ve.command.set({command:key,is_multi:true,element:''});
            },
            items: {
                "delete": {name: "Delete Selected Elements", icon: "delete"},
                "sep1": "---------",
                "quit": {name: "Cancel", icon: "quit"}
            }
        });
    }
    $(window).load(function(){
        ve_iframe_load();

    });
    iframe.init();

})(ve_iframe,window.jQuery);