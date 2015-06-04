/*global jQuery,ve */
var ve =ve || {};
(function (mod,$){


    var ElementsEditor = function(elements) {
        this.elements = elements || [];
        this.is_build_complete = true;
        return this;
    };
    ElementsEditor.prototype = {
        _ajax: false,
        message: false,
        isBuildComplete: function() {
            return this.is_build_complete;
        },
        create: function(attributes) {
            this.is_build_complete = false;
            this.elements.push(ve.elements.create(attributes));
            return this;
        },
        createFromElement:function (e,parent){
            var attributes= _.clone(e.attributes);
            if(!parent) {
                attributes.place_after_id = attributes.id;
            }else{
                attributes.parent_id=parent;
            }
            delete attributes.id;
            delete attributes.from_content;
            this.create(attributes);
            var children;
            if(children=ve.getChildren(e)) {
                var parentId=this.lastID();
                _.forEach(children,function(child_e){this.createFromElement(child_e,parentId)},this)
            }
            return this;
        },

        render: function(callback) {
            var shortcodes;
            shortcodes = _.map(this.elements, function(element){
                var string = element.toString();
                return {id: element.get('id'), shortcode: string, id_base: element.get('id_base')};
            }, this);

            this.build(shortcodes, callback);

        },
        build: function(elements, callback) {
            var that = this;
            this.ajax({action: 've_get_element', elements: elements}).done(function(html){
                that.buildElement(html);
                if(_.isFunction(callback)) callback(html);
                ve.do_action('build_complete',that.elements);
                that.elements = [];
                that.is_build_complete = true;
            });
        },

        buildFromContent: function() {
            var content = $('#ve_template-post-content').html()
                .replace(/<style([^>]*)>\/\*\* ve_js\-placeholder \*\*\//g, '<script$1>')
                .replace(/<\/style([^>]*)><!\-\- ve_js\-placeholder \-\->/g, '</script$1>');
            try {ve.$page.html(content); } catch(e) {}
            _.each(ve.post_elements, function(element){
                var $block = ve.$page.find('[data-element-id=' + element.id + ']'),
                    params = _.isObject(element.attrs) ? element.attrs : {};
                ve.elements.create({
                    id: element.id,
                    id_base: element.id_base,
                    params: params,
                    parent_id: element.parent_id,
                    field_key:element.field_key,
                    from_content: true
                });
                this.buildElementBlock($block.get(0));
                ve.do_action('load_element',element);
            }, this);
            ve.do_action('content_loaded');

        },

        buildElement:function(html,element){
            var new_element,old_view;
            if(element){
                old_view = element.view;
            }
            _.each($(html), function(block){
                    this.buildElementBlock(block);
            }, this);
            if(old_view&&element&&element.view) {
                element.view.$el.insertAfter(old_view.$el);
                if(ve.getChildren(element).length) {
                    old_view.content().find('> *').appendTo(element.view.content());
                }
                old_view.$el.remove();
            }
            if(!element){
                ve.do_action('element_added');
            }
            ve.do_action('build_element',element);
        },
        buildElementBlock: function(block) {
            var $this = $(block), $html, element;
            if($this.data('type')==='files') {
                ve.frame_view.addScripts($this.find('script[src],link'));
            } else {
                element = ve.elements.get($this.data('element-id'));
                $html = $this.is('[data-type=element]') ? $($this.html()) : $this;
                element && element.get('id_base') && this.renderElement($html, element);
            }

            ve.do_action('build_element_block',block);
            return element

        },
        renderElement: function($html, element) {
            var match, view_name = this.getView(element), inner_html = $html, update_inner;
            //js_re = /[^\"](<script\b[^>]+src[^>]+>|<script\b[^>]*>([\s\S]*?)<\/script>)/gm;
            ve.last_inner = inner_html.html();
            $('script', inner_html).each(function () {
                ve.frame_view.doScripts($(this).html());
                $(this).remove();
            });
            if (update_inner) $html.html(inner_html.html());
            !element.get('from_content') && this.placeElement($html, element);
            element.view = new view_name({model: element, el: $html}).render();
            ve.do_action('element_rendered',element);
        },
        getView: function(element) {
            return ve.getElementView(element);
        },
        update: function(model) {
            var shortcode = (model.toString());

            this.ajax({action: 've_get_element', elements: [
                {id: model.get('id'), shortcode: shortcode, id_base: model.get('id_base')}
            ]})
            .done(function(html){

                this.buildElement(html,model);
                ve.do_action('element_updated',model);


            });
        },
        ajax: function(data, url) {
            return this._ajax = $.ajax({
                url: url || ve.admin_ajax,
                type: 'POST',
                dataType: 'html',
                data: _.extend({post_id: ve.post_id, ve_inline: true}, data),
                context: this
            });
        },

        remove: function() {
        },
        _getContainer: function(element) {
            var container, parent_model,
                parent_id = element.get('parent_id');
            if(parent_id !== false) {
                parent_model = ve.elements.get(parent_id);
                if(_.isUndefined(parent_model)) return ve.content_view;
                // parent_model.view === false && this.addShortcode(parent_model);
                container = parent_model.view;
            } else {
                container = ve.content_view;
            }
            return container;
        },
        placeElement: function($html, element) {
            var container = this._getContainer(element);
            container && container.placeElement($html, ve.activity);
            return container;
        },

        elementsToString: function(elements) {
            var string = '';
            _.each(elements, function(element) {
                var tag = element.get('id_base'),
                    params = element.get('params'),
                    content = _.isString(params.content) ? params.content : '';
                content += this.elementsToString(ve.elements.where({parent_id: element.get('id')}));
                string += wp.shortcode.string({
                    tag: tag,
                    attrs: _.omit(params, 'content'),
                    content: content,
                    type: content == '' && !element.setting('container') ? 'self-closing' : ''
                });
            }, this);
            return string;
        },
        getContent: function() {
            ve.elements.sort();
            return this.elementsToString(ve.elements.where({parent_id: false}));
        },
        getTitle: function() {
            return ve.post_title;
        },

        save: function(status) {

            var string = this.getContent(),
                post_data = $('#post').serializeArray();
            var data = {};
            for(var x in post_data) {
                data[post_data[x].name] = post_data[x].value;
            }
            data['content'] = string;
            if(status) {
                data.post_status = status;
                $('.ve_button_save_draft').hide(100);
            }
            if(ve.update_title) data.post_title = this.getTitle();
            this.ajax(data, 'post.php')
                .done(function(){
                    ve.do_action('post_saved');
                });
        },

        /**
         * Unescape double quotes in params valus.
         * @param value
         * @return {*}
         */
        unescapeParam:function (value) {
            return value.replace(/(\`{2})/g, '"');
        },
        setResultMessage: function(string) {
            this.message = string;
        },
        showResultMessage: function() {
            if(this.message !== false) ve.log(this.message);
            this.message = false;
        },
        lastID: function() {
            return this.elements.length ? _.last(this.elements).get('id') : '';
        },
        last: function() {
            return this.elements.length ? _.last(this.elements) : false;
        },
        firstID: function() {
            return this.elements.length ? _.first(this.elements).get('id') : '';
        },
        first: function() {
            return this.elements.length ? _.first(this.elements) : false;
        }
    };
    mod.Editor=ElementsEditor;
    mod.editor=mod.the_editor=new mod.Editor();

})(ve,jQuery);

/**
 * Shortcut
 */



(function($) {

    /** Special keys */
    var special = {
        'backspace': 8,
        'tab': 9,
        'enter': 13,
        'pause': 19,
        'capslock': 20,
        'esc': 27,
        'space': 32,
        'pageup': 33,
        'pagedown': 34,
        'end': 35,
        'home': 36,
        'left': 37,
        'up': 38,
        'right': 39,
        'down': 40,
        'insert': 45,
        'delete': 46,
        'f1': 112,
        'f2': 113,
        'f3': 114,
        'f4': 115,
        'f5': 116,
        'f6': 117,
        'f7': 118,
        'f8': 119,
        'f9': 120,
        'f10': 121,
        'f11': 122,
        'f12': 123,
        '?': 191, // Question mark
        'minus': $.browser.opera ? [109, 45] : $.browser.mozilla ? 109 : [189, 109],
        'plus': $.browser.opera ? [61, 43] : $.browser.mozilla ? [61, 107] : [187, 107]
    };

    /** Hash for shortcut lists */
    var lists = {};

    /** Active shortcut list */
    var active;

    /** Hash for storing which keys are pressed at the moment. Key - ASCII key code (e.which), value - true/false. */
    var pressed = {};
    var timeout = {};

    var isStarted = false;

    var getKey = function(type, maskObj) {
        var key = type;

        if (maskObj.ctrl) { key += '_ctrl'; }
        if (maskObj.alt) { key += '_alt'; }
        if (maskObj.shift) { key += '_shift'; }

        var keyMaker = function(key, which) {
            if (which && which !== 16 && which !== 17 && which !== 18) { key += '_' + which; }
            return key;
        };

        if ($.isArray(maskObj.which)) {
            var keys = [];
            $.each(maskObj.which, function(i, which) {
                keys.push(keyMaker(key, which));
            });
            return keys;
        } else {
            return keyMaker(key, maskObj.which);
        }
    };

    var getMaskObject = function(mask) {
        var obj = {};
        var items = mask.split('+');

        $.each(items, function(i, item) {
            if (item === 'ctrl' || item === 'alt' || item === 'shift') {
                obj[item] = true;
            } else {
                obj.which = special[item] || item.toUpperCase().charCodeAt();
            }
        });

        return obj;
    };

    var checkIsInput = function(target) {
        var name = target.tagName.toLowerCase();
        var type = target.type;
        return (name === 'input' && $.inArray(type, ['text', 'password', 'file', 'search']) > -1) || name === 'textarea';
    };

    var getSelectedText=function(e){
        var text = "";
        if (typeof window.getSelection != "undefined") {
            text = window.getSelection().toString();
        } else if (typeof document.selection != "undefined" && document.selection.type == "Text") {
            text = document.selection.createRange().text;
        }
        return text;
    };

    var run = function(type, e , active) {
        if (!active) { return; }

        var maskObj = {
            ctrl: e.ctrlKey,
            alt: e.altKey,
            shift: e.shiftKey,
            which: e.which
        };

        var key = getKey(type, maskObj);
        //console.log(key);
        var shortcuts = active[key]; // Get shortcuts from the active list.
        //console.log(active);
        if (!shortcuts) { return; }

        var isInput = checkIsInput(e.target);

        $.each(shortcuts, function(i, shortcut) {
            // If not in input or this shortcut is enabled in inputs.
            if (
                (typeof shortcut.disabled == 'function' && shortcut.disabled(e.target)) ||
                (typeof shortcut.disabled == 'boolean' && shortcut.disabled) ||
                (!isInput || shortcut.enableInInput)
            ) {
                if(
                    (typeof shortcut.prevent=='function' && shortcut.prevent(e.target)) ||
                    (typeof shortcut.prevent=='boolean' && shortcut.prevent)
                ){
                    e.preventDefault();
                }
                shortcut.handler(e); // Run the shortcut's handler.
            }
        });
    };

    $.Shortcuts = {};

    /**
     * Start reacting to shortcuts in the specified list.
     * @param {Object} [target] List name
     * @param {String} [list] List name
     */
    $.Shortcuts.start = function(target,list) {
        list = list || 'default';
        //active = lists[list]; // Set the list as active.

        //if (isStarted) { return; } // We are going to attach event handlers only once, the first time this method is called.
        target=target||document;
        $(target).data('ve_shortcut',list).on(($.browser.opera ? 'keypress' : 'keydown') + '.shortcuts', function(e) {
            // For a-z keydown and keyup the range is 65-90 and for keypress it's 97-122.
            if (e.type === 'keypress' && e.which >= 97 && e.which <= 122) {
                e.which = e.which - 32;
            }
            var shortcut_list=$(this).data('ve_shortcut');

            if (!pressed[e.which]) {
                run('down', e , lists[shortcut_list]);
            }
            pressed[e.which] = true;
            clearTimeout(timeout[e.which]);
            timeout[e.which]=setTimeout(function(){pressed[e.which]=false},200);
            run('hold', e, lists[shortcut_list]);
        });

        $(target).on('keyup.shortcuts', function(e) {
            var shortcut_list=$(this).data('ve_shortcut');
            pressed[e.which] = false;
            run('up', e , lists[shortcut_list]);
        });

        isStarted = true;

        return this;
    };

    /**
     * Stop reacting to shortcuts (unbind event handlers).
     */
    $.Shortcuts.stop = function() {
        $(document).off('keypress.shortcuts keydown.shortcuts keyup.shortcuts');
        isStarted = false;
        return this;
    };

    /**
     * Add a shortcut.
     * @param {Object}   params         Shortcut parameters.
     * @param {String}  [params.type]   The type of event to be used for running the shortcut's handler.
     *     Possible values:
     *     down â€“ On key down (default value).
     *     up   â€“ On key up.
     *     hold â€“ On pressing and holding down the key. The handler will be called immediately
     *            after pressing the key and then repeatedly while the key is held down.
     *
     * @param {String}   params.mask    A string specifying the key combination.
     *     Consists of key names separated by a plus sign. Case insensitive.
     *     Examples: 'Down', 'Esc', 'Shift+Up', 'ctrl+a'.
     *
     * @param {Function} params.handler A function to be called when the key combination is pressed. The event object will be passed to it.
     * @param {String}  [params.list]   You can organize your shortcuts into lists and then switch between them.
     *     By default shortcuts are added to the 'default' list.
     * @param {Boolean} [params.enableInInput] Whether to enable execution of the shortcut in input fields and textareas. Disabled by default.
     * @param {Boolean|function} [params.prevent]
     * @param {Boolean|function} [params.disabled]
     */
    $.Shortcuts.add = function(params) {
        if (!params.mask) { throw new Error("$.Shortcuts.add: required parameter 'params.mask' is undefined."); }
        if (!params.handler) { throw new Error("$.Shortcuts.add: required parameter 'params.handler' is undefined."); }

        var type = params.type || 'down';
        var listNames = params.list ? params.list.replace(/\s+/g, '').split(',') : ['default'];

        $.each(listNames, function(i, name) {
            if (!lists[name]) { lists[name] = {}; }
            var list = lists[name];
            var masks = params.mask.toLowerCase().replace(/\s+/g, '').split(',');

            $.each(masks, function(i, mask) {
                var maskObj = getMaskObject(mask);
                var keys = getKey(type, maskObj);
                if (!$.isArray(keys)) { keys = [keys]; }

                $.each(keys, function(i, key) {
                    if (!list[key]) { list[key] = []; }
                    list[key].push(params);
                });
            });
        });

        return this;
    };

    /**
     * Remove a shortcut.
     * @param {Object}  params       Shortcut parameters.
     * @param {String} [params.type] Event type (down|up|hold). Default: 'down'.
     * @param {String}  params.mask  Key combination.
     * @param {String} [params.list] A list from which to remove the shortcut. Default: 'default'.
     */
    $.Shortcuts.remove = function(params) {
        if (!params.mask) { throw new Error("$.Shortcuts.remove: required parameter 'params.mask' is undefined."); }

        var type = params.type || 'down';
        var listNames = params.list ? params.list.replace(/\s+/g, '').split(',') : ['default'];

        $.each(listNames, function(i, name) {
            if (!lists[name]) { return true; } // continue
            var masks = params.mask.toLowerCase().replace(/\s+/g, '').split(',');

            $.each(masks, function(i, mask) {
                var maskObj = getMaskObject(mask);
                var keys = getKey(type, maskObj);
                if (!$.isArray(keys)) { keys = [keys]; }

                $.each(keys, function(i, key) {
                    delete lists[name][key];
                });
            });
        });

        return this;
    };

}(jQuery));
