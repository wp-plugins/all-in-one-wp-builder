(function ($) {
    if(_.isUndefined(window.ve)) window.ve = {};
    /**
     * Element model
     * @type {*}
     */
    var Element = Backbone.Model.extend({
        defaults:function () {
            var id = ve_guid();
            return {
                id:id,
                id_base:'ve_text_block',
                order: ve.elements.nextOrder(),
                params:{
                },
                parent_id:false
            };
        },
        settings: false,
        closest:function(parent){
            parent=ve.getElement(parent);
            if(!parent){
                return false;
            }
            var parent_id=parent.get('id');
            var element=this;
            do{
                if(element.get('id')==parent_id) return parent;
            }while(element=ve.getElement(element.get('parent_id')));
        },
        getParam: function(key) {
            return _.isObject(this.get('params')) && !_.isUndefined(this.get('params')[key]) ? this.get('params')[key] : '';
        },
        getAtts: function (key){
            return _.isObject(this.get('params')) && !_.isUndefined(this.get('params')[key]) ? this.get('params')[key] : '';
        },
        sync: function () {
            return false;
        },
        setting: function(name) {
            if(this.settings === false) this.settings = ve.getElementSetting(this.get('id_base')) || {};
            return this.settings[name];
        },
        getSettings:function(){
            this.setting();
            return this.settings;
        },
        toString:function(type){
            var params = this.get('params'),
                content = _.isString(params.content) ? params.content : '';
            if(_.isUndefined(type)){
                type=this.setting('type');
                if(_.isUndefined(type)){
                    type = content == '' && !this.setting('container') ? 'self-closing' : '';
                }
            }

            return wp.shortcode.string({
                tag: this.get('id_base'),
                attrs: _.omit(params, 'content'),
                content: content,
                type:_.isString(type) ? type : ''
            });
        },
        set : function(key, val, options) {
            if(typeof key=="object"){
                if(key.params){
                    key.params= _.clone(key.params);
                    key.params=ve.apply_filters('element_params',key.params);
                }
            }
            if(key=="params"){
                val= _.clone(val);
                val=ve.apply_filters('element_params',val);
            }
            return Backbone.Model.prototype.set.call(this, key, val,options);
        },
        view: false
    });
    /**
     * Collection of all Elements.
     * @type {*}
     */
    var Elements = Backbone.Collection.extend({
        model: Element,
        sync: function () {
            return false;
        },
        nextOrder: function() {
            if (!this.length) return 1;
            return this.last().get('order') + 1;
        },
        initialize:function () {
            this.bind('remove', this.removeChildren, this);

        },
        comparator:function (model) {
            return model.get('order');
        },
        /**
         * Remove all children of the element from storage.
         * Will remove children of children elements too.
         * @param parent - element which is parent
         */
        removeChildren: function (parent) {
            var models = ve.elements.where({parent_id:parent.id});
            _.each(models, function (model) {
                model.destroy();
                // this.removeChildren(model);
            }, this);
        }
    });
    ve.Element=Element;
    ve.elements = new Elements;
})(window.jQuery);