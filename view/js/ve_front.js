(function(root,factory){
    root.VeFront = factory(root, {}, root._, (root.jQuery || root.Zepto || root.ender || root.$));
}(window,function(root,VeFront,_,$) {
    VeFront = function (options) {
        this.cid = _.uniqueId('veFront');
        options || (options = {});
        _.extend(this, _.pick(options, viewOptions));
        this.selector=this.el;
        this.init.apply(this, arguments);
    };

    VeFront.$=$;
    VeFront.jQuery=$;
// Cached regex to split keys for `delegate`.
    var delegateEventSplitter = /^(\S+)\s*(.*)$/;

// List of view options to be merged as properties.
    var viewOptions = ['el', 'id', 'attributes', 'className', 'tagName'];

// Set up all inheritable **Backbone.View** properties and methods.
    _.extend(VeFront.prototype, {

        // The default `tagName` of a View's element is `"div"`.
        tagName: 'div',

        // jQuery delegate for element lookup, scoped to DOM elements within the
        // current view. This should be preferred to global lookups where possible.
        $: function (selector) {
            return this.$el.find(selector);
        },

        // Initialize is an empty function by default. Override it with your own
        // initialization logic.
        init: function () {
        },
        beforeStart:function(){

        },
        start:function(reinit){
            VeFront.jQuery=jQuery;
            this.beforeStart();
            this._ensureElement();
            var that=this;
            this.$el.each(function(){
                var instance=VeFront.jQuery(this);
                if(!instance.data('ve-front')){
                    instance.data('ve-front','1');
                    that.setup.call(that,instance);
                    if(that.isIframe()) {
                        that.setupIframe.call(that, instance);
                    }
                }
            });
        },
        /**
         * function to setup instance
         * @param instance
         */
        setup:function(instance){

        },
        setupIframe:function(instance){
            var $=VeFront.jQuery;

            $('a',instance).each(function(){
                var $a=$(this);
                if(!$a.data('ve-prevent')){
                    $a.data('ve-prevent',"1");
                    var event= $._data($a.get(0),'events');
                    if(event&&event.click) {
                        $a.on('click',function(e){
                            e.preventDefault();
                        });
                    }else{
                        $a.on('click', function (e) {
                            e.preventDefault();
                            var link = $(this).attr('href');

                            if (link) {
                                window.open(link, '_blank', "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");
                            }
                        });
                    }
                }
            })
        },

        // **render** is the core function that your view should override, in order
        // to populate its element (`this.el`), with the appropriate HTML. The
        // convention is for **render** to always return `this`.
        render: function () {
            return this;
        },

        // Remove this view by taking the element out of the DOM, and removing any
        // applicable Backbone.Events listeners.
        remove: function () {
            this.$el.remove();
            return this;
        },

        // Change the view's element (`this.el` property), including event
        // re-delegation.
        setElement: function (element, delegate) {

            this.$el = VeFront.jQuery(element);
            this.el = this.$el[0];

            return this;
        },
        isIframe:function(){
            return typeof parent.ve !="undefined";
        },


        // Ensure that the View has a DOM element to render into.
        // If `this.el` is a string, pass it through `$()`, take the first
        // matching element, and re-assign it to `el`. Otherwise, create
        // an element from the `id`, `className` and `tagName` properties.
        _ensureElement: function () {
            this.setElement(this.selector);
        }

    });
    var extend = function(protoProps, staticProps) {
        var parent = this;
        var child;

        // The constructor function for the new subclass is either defined by you
        // (the "constructor" property in your `extend` definition), or defaulted
        // by us to simply call the parent's constructor.
        if (protoProps && _.has(protoProps, 'constructor')) {
            child = protoProps.constructor;
        } else {
            child = function(){ return parent.apply(this, arguments); };
        }

        // Add static properties to the constructor function, if supplied.
        _.extend(child, parent, staticProps);

        // Set the prototype chain to inherit from `parent`, without calling
        // `parent`'s constructor function.
        var Surrogate = function(){ this.constructor = child; };
        Surrogate.prototype = parent.prototype;
        child.prototype = new Surrogate;

        // Add prototype properties (instance properties) to the subclass,
        // if supplied.
        if (protoProps) _.extend(child.prototype, protoProps);

        // Set a convenience property in case the parent's prototype is needed
        // later.
        child.__super__ = parent.prototype;

        return child;
    };
    VeFront.extend=extend;
    return VeFront;
}));
var ve_front=ve_front||{};
ve_front.ready=function(callback){
    if(typeof callback=='function'){
        if(typeof jQuery=='function'){
            callback(jQuery);
        }else{
            callback();
        }
    }
};