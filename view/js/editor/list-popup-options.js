/**
 * Created by Alt on 6/25/2015.
 */
(function(ve,$){
    var list;
    var ajax=function(data){
        return ve.ajax(data,'json');
    };
    var ListOptions=Backbone.View.extend({
        events:{
            "click .row-actions .delete":"deleteOption",
            "click .row-actions .edit":"editOption",
            "click [data-filter]":"filterResult",
            "click .pagination-links a":"onPagination"
        },
        initialize: function(option) {
            var self=this;
            this.$topnav=this.$('.tablenav.top');
            this.$bottomnav=this.$('.tablenav.bottom');
            this.$tabs=this.$el.closest('.ve-ui-tabs');
            this.$form=this.$tabs.closest('.ve-ajax-form');
            this.$pOption=this.$tabs.find('.edit-poptions');
            this.$editBtn=this.$tabs.find('[data-edit-popup-option]');
            this.$submitBtn=this.$form.find('#submit_btn');
            this.$tabs.on( "tabsbeforeactivate", function( event, ui ) {
                if(ui.newTab.find('#add_new_btn').length){
                    self.init_new_option();
                }else{
                    if(ui.newTab.find('[data-edit-popup-option]').length){
                        self.init_edit_option();
                    }else{
                        self.$submitBtn.hide()
                    }
                }
            } );
        },
        editOption:function(e){
            e.preventDefault();
            var line=$(e.currentTarget).closest('tr');
            this.option_id=line.data('option-id');
            this.option=line.data('option');
            this.edit_option=line.data('p-title');
            this.$editBtn.trigger('click');
            return false;
        },
        reloadPopupList:function(){
            var self=this;
            ajax({action:"ve_get_popup_options"}).done(function(data){
                if(data) {
                    var $html='';
                    $.each(data, function (k, v) {
                        $html += '<option value="' + k + '">' + v + '</option>';
                    });
                    self.$pOption.find('#ve_poption_popup').html($html);
                }
            });
        },
        init_new_option:function(){
            this.$pOption.find('.popup_title').html(this.edit_option).hide();
            this.$pOption.find('#ve_poption_popup').show();
            this.$pOption.find(':input').val(null).trigger('change');
            this.$form.trigger("reset");
            this.$submitBtn.show();
        },
        init_edit_option:function(){
            this.$pOption.find('.popup_title').html(this.edit_option).show();
            this.$pOption.find('#ve_poption_popup').hide();
            this.set_form('option_id',this.option_id);
            this.set_form('position',this.option.position);
            this.set_form('top',this.option.top);
            this.set_form('left',this.option.left);
            this.set_form('bottom',this.option.bottom);
            this.set_form('right',this.option.right);
            this.set_form('placement',this.option.placement).trigger('change');
            this.set_select2_val('#popup_post',this.option.popup_post);
            this.set_select2_val('#popup_page',this.option.popup_page);
            this.set_select2_val('#popup_category',this.option.popup_category);
            this.set_form('open',this.option.open).trigger('change');
            this.set_form('delay',this.option.delay);
            this.set_form('inactive',this.option.inactive);

            this.$editBtn.show();
            this.$submitBtn.show();
        },
        set_form:function(name,val){
            function escapeStr(str)
            {
                if (str)
                    return str.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');

                return str;
            }
            name=escapeStr(name);
            return this.$form.find('[name='+name+']').val(val);
        },
        set_select2_val:function($element,data){
            $element=$($element);
            var $html='';
            if(data) {
                $.each(data, function (k, v) {
                    $html += '<option value="' + v.id + '" selected="selected">' + v.text + '</option>';
                });
            }
            $element.html($html);
            var select2=$element.data('select2');
            if(select2&&select2.options&&data){
                var oldoptions=select2.options.options;
                var options={
                    width:oldoptions.width,
                    ajax:oldoptions.ajax,
                    minimumInputLength:oldoptions.minimumInputLength
                };
                $element.select2(options);
            }
        },

        deleteOption:function(e){
            e.preventDefault();
            var self=this;
            if(confirm('Are you sure to delete this option')){
                var option_id=$(e.currentTarget).closest('tr').data('option-id');

                ajax({
                    action:'ve_delete_popup_option',
                    option_id:option_id,
                    paged:this.paged,
                    "option-filter":this.filter
                }).done(
                    function(response){
                        if(response.paged){
                            self.paged=response.paged;
                        }
                        $('#ve-popup-option-'+option_id).fadeOut(400,function(){
                            self.setContent(response);
                        });
                        self.$editBtn.hide();
                        self.reloadPopupList();
                    }
                );
            }
        },
        setContent:function(data){
            if(data.rows){
                this.$('tbody').html(data.rows);
            }
            if(data.top_nav){
                this.$topnav.find('.tablenav-pages').replaceWith(data.top_nav);
            }
            if(data.bottom_nav){
                this.$bottomnav.find('.tablenav-pages').replaceWith(data.bottom_nav);
            }
        },
        onPagination:function(e){
            e.preventDefault();
            var link=$(e.currentTarget);
            if(link.hasClass('disabled'))
                return false;
            if(!link.length){
                return false;
            }
            var query=link.get(0).search;
            if(query){
                query=query.substr(1);
            }
            var params={};
            ve.php.parse_str(query,params);
            if(params.paged) {
                this.paged=params.paged;
                this.loadPage();
            }
            return false;
        },
        loadPage:function(paged){
            var self=this;
            paged=ve.php.intval(paged);
            if(paged>0) {
                this.paged = paged;
            }
            ajax({action:'ve_list_popup_options',paged:this.paged,"option-filter":this.filter}).done(function(data){
                self.setContent(data);
            });
        },
        filterResult:function(e){
            this.filter = $(e.currentTarget).data('filter');
            this.loadPage();
        }
    });

    ve.add_action('load',function(){
        list=new ListOptions({el:$('.ve-list-popup-options').closest('.ve-list-table')});
    });
    ve.add_action('ajax_form_done_ve_add_popup_option',function(req,response){
        list.setContent(response);
        list.reloadPopupList();
    });
})(ve,jQuery);