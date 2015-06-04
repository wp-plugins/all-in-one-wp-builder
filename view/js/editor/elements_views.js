(function ($) {
    ve.element_views.ElementViewAbstract = Backbone.View.extend({
        hold_hover_on: false,
        events: {

            'click .ve_controls .element-name':'select',
            'click .ve_controls [data-cmd]':'cmd'
        },
        controls_set: false,
        $controls: false,
        $content: false,
        $contentWrap:false,
        move_timeout: false,
        out_timeout: false,
        hold_active: true,
        view_rendered: false,
        initialize: function () {
            this.listenTo(this.model, 'destroy', this.removeView);
            this.listenTo(this.model, 'change:parent_id', this.changeParentId);
            self = this;
            //this.$el.on('click','a',this.select)
        },
        render: function () {
            this.$el.attr('data-element-id', this.model.get('id'));

            this.$el.attr('data-id-base', this.model.get('id_base'));
            if(!this.model.setting('container')) {
                this.$el.addClass('ve-el');
            }
            this.$el.addClass('ve_element-' + this.model.get('id_base'));

            this.rendered();
            return this;
        },
        select:function(e){
            if(e) {
                e.stopPropagation();
            }
            ve.content_view.$el.addClass('ve_multiple_selected');
            if(!e||!e.ctrlKey){
                ve.content_view.clearSelected();
                ve.content_view.$el.removeClass('ve_multiple_selected');
            }
            this.cleanSelectTree(this.$el);
            this.$el.addClass('ve_selected');
            if(e) {
                ve.command.set({command: 'select', element: this.model.id, multi: e.ctrlKey});
            }
            var parent_view=this;
            if(this.getParent(parent_view)) {
                while (parent_view = this.getParent(parent_view)) {
                    parent_view.$el.addClass('ve_selected-parent');
                }
            }else{
                parent_view.$el.addClass('ve_selected-parent');
            }
        },
        cmd:function(e){

            if(e){
                e.stopPropagation();
                e.preventDefault();
                var cmd=$(e.currentTarget).data('cmd');
                ve.command.set({command: cmd, element: this.model.id});
            }

        },
        cleanSelectTree:function($el){
            //remove selected child
            $el.find('.ve_selected').removeClass('ve_selected');
            var parent=$el.parent();
            while(parent.length&&!parent.is('.ve_post_content')&&!parent.is('body')){
                parent.removeClass('ve_selected');
                parent=parent.parent();
            }
        },
        getParent:function(view){
            view=view||this;
            if(!view||!view.model||!view.model.get('parent_id')){
                return false;
            }
            if(view.model.get('parent_id')==view.model.get('id')){
                return false;
            }
            return ve.getViewById(view.model.get('parent_id'));
        },
        getControls:function(){
            if(!this.$controls){
                this.$controls=$('#ve_element-'+this.model.get('id_base')+'-control');
                if(this.$controls.length<=0){
                    this.$controls=$('#ve_element-'+'default'+'-control');
                }
                if(this.$controls.length<0){
                    this.$controls = $('<div class="no-controls"></div>');
                }
            }
            var controls= _.template(this.$controls.html());
            return $(controls(this.model.getSettings())).addClass('ve_controls');
        },

        updateTree:function(direction){
            direction=direction||'both';
            if(direction=='up'){
                this.updateTreeUp();
            }else if(direction=='down'){
                this.updateTreeDown();
            }else{
                this.updateTreeUp();
                this.updateTreeDown();
            }
        },
        onUpdateTreeUp:function(){
            console.log('updated up:'+this.model.get('id_base'));

        },
        /**
         * update up element tree from it's parent
         */
        updateTreeUp: function (previousElement) {
            var parent;
            if(previousElement) {
                this.onUpdateTreeUp(previousElement);
            }
            if(parent=this.getParent()){
                parent.updateTreeUp(this.model);
            }
        },
        onUpdateTreeDown:function(){
            console.log('updated down:'+this.model.get('id_base'));
        },
        /**
         * update down element tree
         */
        updateTreeDown:function(){
            _.each(ve.elements.where({parent_id: this.model.get('id')}), function (model) {
                model.view.parent_view = this;
                model.view.onUpdateTreeDown(this.model);
            }, this);
        },
        /**
         * call when an element updated, no any other trigger on element tree
         */
        update:function(){
            this.updated();
        },
        updated:function(){

        },

        parentChanged: function () {

        },
        onContentLoaded:function(){//call when all content loaded and view created

        },
        /**
         * fire one time once view rendered
         */
        rendered: function () {
            if(!this.view_rendered) {
                this.addControls();
                this.changed();
                this.view_rendered=true;
            }
            return this;
        },
        addControls: function () {
            if(this.controls_set){
                return this;
            }
            this.controls_set=true;
            this.getControls().appendTo(this.$el);
            return this;
        },
        content: function () {
            if (this.$content === false) this.$content = this.$el.find('> :first');
            return this.$content;
        },
        changeParentId: function () {
            var parent_id = this.model.get('parent_id'), parent;
            if (parent_id === false) {
                ve.view.placeElement(this.$el);
            } else {
                parent = ve.elements.get(parent_id);
                parent && parent.view && parent.view.placeElement(this.$el);
            }
            this.parentChanged();
        },

        changed: function () {

        },

        removeView: function (element) {
            this.remove();
            if(element.get('parent_id')){
                var parent=ve.getElementById(element.get('parent_id'));
                if(parent&&parent.setting('lv')==1&&ve.getChildren(parent).length==1){
                    parent.destroy();
                }else {

                }
            }
        },

        getParam: function (param_name) {
            return _.isObject(this.model.get('params')) && !_.isUndefined(this.model.get('params')[param_name]) ? this.model.get('params')[param_name] : null;
        },
        beingCut:function(){
            ve.content_view.clearCutting();
            this.$el.addClass('ve_cutting');
        },
        placeElement: function ($view, activity) {
            var model = ve.elements.get($view.data('element-id'));
            if (model && model.get('place_after_id')) {
                $view.insertAfter(ve.$page.find('[data-element-id=' + model.get('place_after_id') + ']'));
                model.unset('place_after_id');
            } else if (_.isString(activity) && activity === 'prepend') {
                $view.prependTo(this.content());
            } else {
                $view.appendTo(this.content());
            }
            this.changed();
        },
        getParentView:function(){
            var parent=ve.getElement(this.model.get('parent_id'));
            if(parent){
                return parent.view;
            }
            return false;
        },
        is:function(id_base){
            return this.model.get('id_base')==id_base;
        }
    });

    ve.element_views.ElementViewContainer = ve.element_views.ElementViewAbstract.extend({
        events: _.extend({},ve.element_views.ElementViewAbstract.prototype.events,{
            'mouseenter': 'resetActive',
            'mouseleave': 'holdActive'
        }),
        hold_active: 0,
        initialize: function (params) {
            _.bindAll(this, 'holdActive');
            ve.element_views.ElementViewContainer.__super__.initialize.call(this, params);
        },
        resetActive: function() {
            this.hold_active && window.clearTimeout(this.hold_active);
        },
        holdActive: function() {
            this.resetActive();
            this.$el.addClass('ve_hover');
            var view = this;
            this.hold_active = window.setTimeout(function(){
                view.hold_active && window.clearTimeout(view.hold_active);
                view.hold_active = 0;
                view.$el.removeClass('ve_hover');
            }, 700);
        },
        content: function () {
            if(this.$content === false) {
                this.$content = this.$('.ve_container_anchor:first').parent();
                this.$('.ve_container_anchor:first').remove();
            }
            return this.$content;
        },
        contentWrapper: function(){
            if(this.$contentWrap===false){
                this.$contentWrap=this.$('> :first');
            }
            return this.$contentWrap;
        },
        render: function () {
            ve.element_views.ElementViewContainer.__super__.render.call(this);
            this.content().addClass('ve_element_container');
            this.$el.addClass('ve_container_wrapper');
            this.contentWrapper().addClass('ve_element_wrapper');
            return this;
        },
        changed: function () {
            (this.$el.find('.ve_element[data-id-base]').length == 0 &&
            this.$el.addClass('ve_empty').find('> :first').addClass('ve_empty-element'))
            || this.$el.removeClass('ve_empty').find('> .ve_empty-element').removeClass('ve_empty-element');
        }


    });

    ve.element_views.ElementView_ve_row=ve.element_views.ElementViewContainer.extend({
        column_tag: 've_col',
        total_column:12,
        initialize:function(params){
            ve.element_views.ElementView_ve_row.__super__.initialize.call(this, params);
        },


        addColumn: function() {
            ve.the_editor.create({
                id_base: this.column_tag,
                parent_id: this.model.get('id')
            }).render();
        },
        addElement: function(e) {
            e && e.preventDefault();
            this.addColumn();
        },


        changed: function() {
            ve.element_views.ElementView_ve_row.__super__.changed.call(this);
            console.log('row changed:'+this.model.get('id'));
            ve.content_loaded&&this.setColumnPlaceHolder();//for modify action
            if(ve.getChildren(this.model).length==0&&ve.content_loaded){//remove empty row
                this.model.destroy();
            }

        },
        onContentLoaded:function(){
            this.setColumnPlaceHolder();
        },
        onUpdateTreeUp:function(from){
            if(from.get('id_base')==this.column_tag){
                this.setColumnPlaceHolder();
            }
        },
        updated:function(){
            this.changed();

        },
        setColumnPlaceHolder:function(){
            var columns=ve.getChildren(this.model);
            var columnsUsed=0;
            if(columns.length){
                _.each(columns,function(column){
                    var width = column.getParam('width') || '1/1';
                    var offset = column.getParam('offset') || '0/12';
                    columnsUsed+=this.getColumnNumber(width);
                    columnsUsed+=this.getColumnNumber(offset);
                },this);
            }
            var columnEmpty=this.total_column-columnsUsed,
                $columnPlaceHolder=this.$el.find('.ve_col-placeholder');
            $columnPlaceHolder.remove();
            var row_height=this.$el.height();
            if(columnEmpty>0){
                var row_id=this.model.get('id');
                $columnPlaceHolder = $('<div class="ve_col-placeholder"></div>');
                $columnPlaceHolder.addClass('ve-col-sm-'+columnEmpty);
                $columnPlaceHolder.attr('data-row-id',row_id).data('row-id',row_id);
                $columnPlaceHolder.attr('data-columns',columnEmpty).data('columns',columnEmpty);
                this.content().append($columnPlaceHolder);
                $columnPlaceHolder.height(row_height);
                ve.content_view.columnHolderDroppable();
            }
        },
        getColumnNumber:function(width){
            var
                numbers = width ? width.split('/') : [1,1],
                range = _.range(0,13),
                num = !_.isUndefined(numbers[0]) && _.indexOf(range, parseInt(numbers[0], 10)) >=0 ? parseInt(numbers[0], 10) : false,
                dev = !_.isUndefined(numbers[1]) && _.indexOf(range, parseInt(numbers[1], 10)) >=0 ? parseInt(numbers[1], 10) : false;

            if(num!==false && dev!==false) {
                return  (12*num/dev);
            }
            return 0;
        }

    });

    ve.element_views.ElementView_ve_col=ve.element_views.ElementViewContainer.extend({
        events: _.extend({},ve.element_views.ElementViewContainer.prototype.events,{
            'click.select':'select'
        }),
        initialize:function(params){
            ve.element_views.ElementView_ve_col.__super__.initialize.call(this, params);
        },

        render:function(){
            ve.element_views.ElementView_ve_col.__super__.render.call(this);
            this.setColumnClasses();
            this.updateColumnWidthInfo();
            return this;
        },
        setColumnClasses: function() {
            var offset = this.getParam('offset') || '0/12',
                width = this.getParam('width') || '1/1';
            this.css_class_width = this.convertSize(width,'');
            this.css_class_offset = this.convertSize(offset,'');

            this.contentWrapper().removeClass('ve-col-sm-' + this.css_class_width);
            if(!offset.match(/col\-sm\-\d+/)) {
                this.$el.addClass('ve-col-sm-' + this.css_class_width);
            }

            if(offset!='0/12') {
                this.contentWrapper().removeClass("ve-col-md-offset-" + this.css_class_offset);
                this.$el.addClass("ve-col-md-offset-" + this.css_class_offset);
            }
        },
        updateColumnWidthInfo:function(){
            this.$el.find('.ve_controls .element-width').html(this.getWidth());
        },
        updated:function(){
            this.changed();//it already changed one on rendered so need to recall here
            this.setColumnClasses();
            this.updateColumnWidthInfo();
        },
        onContentLoaded:function(){
            this.updateColumnWidthInfo();
        },
        getWidth:function(){
            var width = this.getParam('width') || '1/1';
            width=this.convertSize(width,'');
            return width;
        },
        convertSize: function(width,prefix) {
            if(typeof prefix=='undefined') {
                prefix = 've-col-sm-';
            }
            var
                numbers = width ? width.split('/') : [1,1],
                range = _.range(0,13),
                num = !_.isUndefined(numbers[0]) && _.indexOf(range, parseInt(numbers[0], 10)) >=0 ? parseInt(numbers[0], 10) : false,
                dev = !_.isUndefined(numbers[1]) && _.indexOf(range, parseInt(numbers[1], 10)) >=0 ? parseInt(numbers[1], 10) : false;

            if(num!==false && dev!==false) {
                return prefix + (12*num/dev);
            }
            return prefix + '12';
        },
        addControls: function () {

            this.getControls().appendTo(this.$el);
            return this;
        }


    });

    ve.element_views.ElementView = ve.element_views.ElementViewAbstract.extend({
        events: _.extend({},ve.element_views.ElementViewAbstract.prototype.events,{
            'click.select':'select'
        }),
        changed: function(){
            this.$el.removeClass('ve_empty-el');
            this.$el.height()===0 && this.$el.addClass('ve_empty-el');
        },
        updated: function(){
            this.changed();
        }

    });
    /*ve.element_views.ElementView_ve_slide=ve.element_views.ElementView.extend({
        changed:function(){
            ve.element_views.ElementView_ve_slide.__super__.changed.call(this);
            ve.frame_view.window.ve_iframe.ve_gallery(this.model.get('id'));
            return this;
        }
    });
    */

})(jQuery);