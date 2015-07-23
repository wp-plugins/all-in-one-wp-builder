<?php
class VE_PopupOption_List_Table extends VE_List_Table{
    var $post_type;
    var $positionOptions;
    var $appearanceOptions;
    var $placementOptions;
    var $openOptions;

    function __construct( $args = array() ){
        parent::__construct($args);
    }
    function bootstrap(){
        $this->post_type=$this->getVeManager()->getPostManager()->post_type_popup;
        wp_enqueue_script('ve_list_popup_options',ve_resource_url(VE_VIEW.'/js/editor/list-popup-options.js'),array(),VE_VERSION,true);
        $this->positionOptions=array(
            'center'=>'Center',
            'top-left'=>'Top left',
            'top-right'=>'Top Right',
            'bottom-left'=>'Bottom left',
            'bottom-right'=>'Bottom Right'
        );
        $this->appearanceOptions=array(
            ''=>'Default',
            'center'=>'Center',
        );
        $this->placementOptions=array(
            ''=>'None',
            'all'=>'Whole site',
            'post'=>'All posts',
            'page'=>'All pages',
            'category'=>'By Category',

        );
        $this->openOptions=array(
            ''=>'Not open automatically',
            'open_on_mouse_out'=>'Open when mouse out of page',
            'open_with_delay'=>'Open after page loaded',
        );
    }

    function get_columns() {
        return $columns= array(
            'cb'=>__('ID'),
            'title'=>__('Title'),
            'action'=>__('Action'),
        );
    }

    /**
     * @param Object $item
     * @return string
     */
    function column_title($item){
        return sprintf('%1$s', $item->title);
    }
    function column_action($item){
        $actions = array(
            'edit'      => sprintf('<a href="#">Edit</a>'),
            'delete'    => sprintf('<a href="#" class="delete-option">Delete</a>',$item->ID),
        );

        return $this->row_actions($actions);
    }
    function column_cb($item){

    }

    function get_sortable_columns(){
        return array();
    }
    function list_info(){
        printf('<input type="hidden" class="list-info" data-list-info=\'%s\'/>',json_encode($this->_args));
    }
    function extra_tablenav($which){
        if($which=='top'){
            $this->list_info();
        }
    }
    function get_views(){
        $filters=array();
        $filters['all']='<a class="" href="#" data-filter="">All</a>';
        $filters['page-loaded']='<a class="" href="#" data-filter="open_with_delay">Page Loaded</a>';
        $filters['mouse-out']='<a class="" href="#" data-filter="open_on_mouse_out">Mouse Out</a>';
        return $filters;
    }
    public function single_row( $item ) {
        printf( '<tr id="ve-popup-option-%1$s" data-option-id="%1$s" data-p-title="%4$s" data-option=\'%3$s\' class="option-item %2$s">' , $item->ID ,$item->class,json_encode($item->option),esc_attr($item->ptitle));
        $this->single_row_columns( $item );
        echo '</tr>';
    }
    function get_pagenum(){
        if(isset($this->current_page)&&$this->current_page>=1){
            return $this->current_page;
        }
        return parent::get_pagenum();
    }
    function get_filter(){
        return isset($_REQUEST['option-filter'])?$_REQUEST['option-filter']:'';
    }
    function prepare_items(){
        $per_page = 5;
        $current_page = $this->get_pagenum();
        $filter=$this->get_filter();
        $query=new WP_Query();
        $args=array(
            'post_type' => $this->post_type,
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_ve_poptions',
                    'compare' => 'EXISTS'
                )
            ),
            'post_status' => array('publish')
        );


        $posts = $query->query($args);
        $popupOptions=array();
        foreach($posts as $p){
            $draf = "";
            if ($p->post_status == "draft"){
                $draf = " <b>(Draft)</b>";
            }
            $ve_options = get_post_meta($p->ID,'_ve_poptions',true);
            if(!is_array($ve_options)){
                continue;
            }
            $p_title=$title=$p->post_title . $draf . ' (' . count($ve_options) .' options)';
            foreach($ve_options as $idx => $option) {
                $str = $title . ': ' . $this->positionOptions[$option['position']] . "_";
                $str .= $this->placementOptions[$option['placement']] . "_";
                $str .= $this->openOptions[$option['open']];

                if($filter&&$option['open']!=$filter){
                    continue ;
                }
                if(!empty($option['popup_post'])){
                    $temp=array();
                    foreach($option['popup_post'] as $p_id){
                        if($p_id&&$post=get_post($p_id)){
                            $temp[]=array('id'=>$p_id,'text'=>$post->post_title);
                        }
                    }
                    $option['popup_post']=$temp;
                }
                if(!empty($option['popup_page'])){
                    $temp=array();
                    foreach($option['popup_page'] as $p_id){
                        if($p_id&&$post=get_post($p_id)){
                            $temp[]=array('id'=>$p_id,'text'=>$post->post_title);
                        }
                    }
                    $option['popup_page']=$temp;
                }
                if(!empty($option['popup_category'])){
                    $temp=array();
                    foreach($option['popup_category'] as $p_id){
                        if($p_id&&$post=get_post($p_id)){
                            $temp[]=array('id'=>$p_id,'text'=>$post->post_title);
                        }
                    }
                    $option['popup_category']=$temp;
                }
                $popupOptions[]=(object)array(
                    'option'=>$option,
                    'ID'=>$p->ID.'-'.(int)$idx,
                    'title'=>$str,
                    'class'=>$option['open'],
                    'ptitle'=>$p_title,
                );
            }
        }

        $total_items=count($popupOptions);

        $this->items=array_slice($popupOptions,($current_page-1)*$per_page,$per_page);
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page                     //WE have to determine how many items to show on a page
        ) );


        if($total_items&&$current_page>1&&empty($this->items)){
            $this->current_page-=1;
            $this->prepare_items();
        }

    }
    function get_total_posts($args){
        $args['offset']=0;
        $args['posts_per_page']=1;
        $query=new WP_Query();
        $query->query($args);
        return $query->found_posts;
    }
    public function ajax_response() {
        $this->prepare_items();

        ob_start();
        if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
            $this->display_rows();
        } else {
            $this->display_rows_or_placeholder();
        }

        $rows = ob_get_clean();

        $response = array( 'rows' => $rows );

        if ( isset( $this->_pagination_args['total_items'] ) ) {
            $response['total_items_i18n'] = sprintf(
                _n( '1 item', '%s items', $this->_pagination_args['total_items'] ),
                number_format_i18n( $this->_pagination_args['total_items'] )
            );
        }
        if ( isset( $this->_pagination_args['total_pages'] ) ) {
            $response['total_pages'] = $this->_pagination_args['total_pages'];
            $response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
        }

        ob_start();
        $this->pagination('top');
        $top_nav = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $bottom_nav=ob_get_clean();

        $response['top_nav']=$top_nav;
        $response['bottom_nav']=$bottom_nav;
        $response['paged']=$this->current_page;
        die( wp_json_encode( $response ) );
    }
}