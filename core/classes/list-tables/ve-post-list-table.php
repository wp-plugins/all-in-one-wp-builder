<?php
class VE_Post_List_Table extends VE_List_Table{
    var $post_type;
    /**
     * @var int current page
     */
    var $current_page;
    /**
     * @param array $args
     */
    function __construct( $args = array() ){
        parent::__construct($args);
        $this->post_type=isset($args['post_type'])?$args['post_type']:'page';
    }

    function get_columns() {
        return $columns= array(
            'cb'=>__('ID'),
            'title'=>__('Title'),
            'date'=>__('Date'),
            'action'=>__('Action'),
        );
    }

    /**
     * @param WP_Post $item
     * @return string
     */
    function column_title($item){
        $draft = $item->post_status == 'draft'?' <b>(Draft)</b>': '';
        return sprintf('%1$s', $item->post_title.$draft);
    }
    function column_action($item){
        $actions = array(
            'clone'    => sprintf('<a href="#" class="clone-post">Clone</a>',$item->ID),
            'edit'      => sprintf('<a href="%s">Edit</a>',get_edit_post_link($item->ID)),
            'delete'    => sprintf('<a href="#" class="delete-post">Delete</a>',$item->ID),
        );
        if($this->getVeManager()->getPostManager()->isTemplate($item)){
            $actions['template']=sprintf('<a href="#" class="load-template-link" data-cmd="loadTemplate" data-template="%s">Load</a>',$item->ID);
        }
        return $this->row_actions($actions);
    }
    function column_cb($item){

    }
    function column_date($post){
        if ( '0000-00-00 00:00:00' == $post->post_date ) {
            $t_time = $h_time = __( 'Unpublished' );
            $time_diff = 0;
        } else {
            $t_time = get_the_time( __( 'Y/m/d g:i:s a' ),$post );
            $m_time = $post->post_date;
            $time = get_post_time( 'G', true, $post );

            $time_diff = time() - $time;

            if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS )
                $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
            else
                $h_time = mysql2date( __( 'Y/m/d' ), $m_time );
        }
        echo '<abbr title="' . $t_time . '">' . $h_time  . '</abbr>';
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
    public function single_row( $item ) {
        printf( '<tr id="ve-post-%1$s" data-post-id="%1$s">' , $item->ID );
        $this->single_row_columns( $item );
        echo '</tr>';
    }
    function get_pagenum(){
        if(isset($this->current_page)&&$this->current_page>=1){
            return $this->current_page;
        }
        return parent::get_pagenum();
    }
    function prepare_items(){
        $per_page = 5;
        $current_page = $this->get_pagenum();
        $query=new WP_Query();
        $args=array(
            'offset'=>($current_page-1)*$per_page,
            'post_type' => $this->post_type,
            'posts_per_page' => $per_page,
            'meta_key' => '_use_ve',
            'meta_value' => '1',
            'post_status' => array('publish','draft'));
        $ve_post_types=$this->getVeManager()->getPostManager()->getPostTypes(true);
        if(in_array($this->post_type,$ve_post_types)){
            unset($args['meta_key'],$args['meta_value']);
        }
        $posts = $query->query($args);
        $total_items=$this->get_total_posts($args);
        $this->items=$posts;
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