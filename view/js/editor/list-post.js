var ve=ve||{};
(function(ve,$){
    var list={};
    var ajax=function(data){
        return ve.ajax(data,'json');
    };
    var ListPost=Backbone.View.extend({
        events:{
            "click .row-actions .delete-post":"deletePost",
            "click .row-actions .clone-post":"clonePost",
            "click .pagination-links a":"onPagination"
        },

        initialize: function(option) {
            var self=this;
            this.post_type=option.post_type||'';
            this.$topnav=this.$('.tablenav.top');
            this.$bottomnav=this.$('.tablenav.bottom');

        },
        clonePost:function(e){
            e.preventDefault();
            var self=this;
            var post_id=$(e.currentTarget).closest('tr').data('post-id');
            ajax({
                action:'ve_clone_post',
                post_id:post_id,
                post_type:self.post_type,
                paged:this.paged
            }).done(
                function(response){
                    if(response.paged){
                        self.paged=response.paged;
                    }
                    $('#ve-post-'+post_id).fadeOut(400,function(){
                        self.setContent(response);
                    })
                }
            );
        },
        deletePost:function(e){
            e.preventDefault();
            var self=this;
            if(confirm('Are you sure to delete this post')){
                var post_id=$(e.currentTarget).closest('tr').data('post-id');
                ajax({
                    action:'ve_delete_post',
                    post_id:post_id,
                    post_type:self.post_type,
                    paged:this.paged
                }).done(
                    function(response){
                        if(response.paged){
                            self.paged=response.paged;
                        }
                        $('#ve-post-'+post_id).fadeOut(400,function(){
                            self.setContent(response);
                        })
                    }
                );
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
            paged=paged||this.paged;
            ajax({action:'ve_list_posts',post_type:this.post_type,paged:paged}).done(function(data){
                self.setContent(data);
            });
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
        refresh:function(){
            this.loadPage(1);
        }
    });
    ve.on('load',function(){
        $('.ve-manage-list-post').each(function(){
            var args=$('[data-list-info]',this).data('list-info');
            var list_post_type=$(this).data('post_type');
            if(list_post_type) {
                list[list_post_type] = new ListPost({el: this, post_type: list_post_type});
            }
        });
        ve.on('ajax_form_done',function(action,data){
            var list=null;
            if(action=='ve_update_post_meta'||action=='ve_update_post'){
                if(data.post_title){
                    list=ve.getPostListTable(ve.post.post_type);
                }
            }
            if(action=='ve_save_as_template'){
                list=ve.getPostListTable('template');
            }
            if(action=='ve_save_as_element'){
                list=ve.getPostListTable('element');
            }
            if(list){
                list.refresh();
            }
        });
    });

    ve.getPostListTable=function(type){
        ve.postTypes&&ve.postTypes[type]&&(type=ve.postTypes[type]);
        return list[type]||false;
    };
})(ve,jQuery);