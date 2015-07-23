var ve=ve||{};
ve.dialogViews=ve.dialogViews||{};
(function(ve,$){
    var Dialog=Backbone.View.extend({
        events:{
            'click .ve_dialog-close':'hide'
        },
        initialize:function(){
            this.setDraggable();
        },
        setDraggable:function(){
            this.$el.draggable({iframeFix:true,handle:".ve_dialog-header"});
        },
        hide:function(e){
            if(e) {
                e.preventDefault();
                $(e.currentTarget).closest('.ve_dialog').hide();
            }
        },
        show:function(selector,data){
            var dialog=this.$el.filter(selector);
            if(dialog.length==1){
                var view=dialog.data('view');
                if(view&& _.isString(view)){
                    if(typeof ve.dialogViews[view]=='function'){
                        view=new ve.dialogViews[view]({el:selector});
                        dialog.data('view',view);
                    }
                }
                view.render(data);
                dialog.show();
                return dialog;
            }
        }

    });
    ve.on('load',function(){
       ve.dialog=new Dialog({el:'.ve_dialog'});
    });
    ve.dialogViews.rowLayout=Backbone.View.extend({
        events:{
            'click .ve_layout-btn':'changeLayout',
            'click #ve_row-layout-update': 'updateFromInput',
            'change .layout-select': 'changeLayout'
        },
        initialize:function(){
            this.$layoutSelect=this.$('.layout-select');
        },
        render:function(element){
            this.model=ve.getElementById(element);
            this.$input = $('#ve_row-layout');
            this.$layoutSelect.get(0).selectedIndex=0;
            this.addCurrentLayout();
        },
        addCurrentLayout: function() {
            ve.elements.sort();
            var string = _.map(ve.elements.where({parent_id: this.model.get('id')}), function(model) {
                var width = model.getParam('width');
                return !width ? '1/1' : width; // memo + (memo!='' ? ' + ' : '') + model.getParam('width') || '1/1';
            }, '', this).join(' + ');
            this.$input.val(string);
        },
        changeLayout:function(e){
            e&&e.preventDefault();
            if (!this.isBuildComplete()) return false;
            var target=$(e.currentTarget);
            var layout=target.data('cells');
            if(target.is('select')){
                layout=target.val();
            }
            if(layout) {
                var columns = this.model.view.convertRowColumns(layout, this.editor());
                this.$input.val(columns.join(' + '));
            }
        },
        isBuildComplete:function(){
            return this.editor().isBuildComplete();
        },
        editor: function() {
            if(!this._editor) this._editor =  new ve.Editor();
            return this._editor;
        },
        updateFromInput: function(e) {
            e && e.preventDefault();
            if (!this.isBuildComplete()) return false;
            var layout,
                cells = this.$input.val();
            if((layout = this.validateCellsList(cells))!==false) {
                this.model.view.convertRowColumns(layout, this.editor());
            } else {
                window.alert('Invalid layout input');
            }
        },
        validateCellsList: function(cells) {
            var return_cells = [],
                split = cells.replace(/\s/g, '').split('+'),
                b, num, denom;
            var sum = _.reduce(_.map(split, function(c){
                if(c.match(/^[ve\_]{0,1}span\d{1,2}$/)) {
                    var converted_c = this.column_span_convert_size(c);
                    if(converted_c===false) return 1000;
                    b = converted_c.split(/\//);
                    return_cells.push(b[0] + '' + b[1]);
                    return 12*parseInt(b[0], 10)/parseInt(b[1], 10);
                } else if(c.match(/^[1-9]|1[0-2]\/[1-9]|1[0-2]$/)) {
                    b = c.split(/\//);
                    num = parseInt(b[0], 10);
                    denom = parseInt(b[1], 10);
                    if(12%denom!==0 || num > denom) return 1000;
                    return_cells.push(num  + '' + b[1]);
                    return 12*num/denom;
                }
                return 1000;

            }), function(num, memo) {
                memo = memo + num;
                return memo;
            }, 0);
            if(sum >= 1000) return false;
            return return_cells.join('_');
        },
        column_span_convert_size:function (width) {
            width = width.replace(/^ve_/, '');
            if (width == "span12")
                return '1/1';
            else if (width == "span11")
                return '11/12';
            else if (width == "span10") //three-fourth
                return '5/6';
            else if (width == "span9") //three-fourth
                return '3/4';
            else if (width == "span8") //two-third
                return '2/3';
            else if (width == "span7")
                return '7/12';
            else if (width == "span6") //one-half
                return '1/2';
            else if (width == "span5") //one-half
                return '5/12';
            else if (width == "span4") // one-third
                return '1/3';
            else if (width == "span3") // one-fourth
                return '1/4';
            else if (width == "span2") // one-fourth
                return '1/6';
            else if(width == "span1")
                return '1/12';

            return false;
        }

    });


})(ve,jQuery);