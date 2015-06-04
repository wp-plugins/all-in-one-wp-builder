/**
 * Create Unique id for records in storage.
 * Generate a pseudo-GUID by concatenating random hexadecimal.
 * @return {String}
 */
function ve_guid() {
    return (VES4() + VES4() + "-" + VES4());
}

// Generate four random hex digits.
function VES4() {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
}

if(_.isUndefined(ve)) var ve = {};
_.extend(ve, {
    responsive_disabled: false,
    template_options:{
        evaluate:    /<#([\s\S]+?)#>/g,
        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
        escape:      /\{\{([^\}]+?)\}\}(?!\})/g
    },
    element_views:{},
    post_id: '',
    activity: false,
    loaded: false,
    admin_ajax: window.ajaxurl,
    $title: false,
    update_title: false,

    activeEditor:'',
    time:function(){
        return Math.floor(new Date()
            .getTime() / 1000);

    },
    setTemplateOptions:function(){

    },

    php:new PHPJS()
});
_.bindAll(ve.php,'md5');
var ve_filter={};
var ve_action={};
var ve_done_filter={};
ve.on=function(tag,function_to_add,context,priority){
    ve.add_action(tag,function_to_add,context,priority);
};
ve.add_action=function (tag, function_to_add, context, priority) {
    return ve.add_filter(tag,function_to_add,context,priority);
};
function _ve_filter_build_unique_id(tag, func, priority){
    return tag+':'+priority+':'+ve.php.md5(func.toString());
}

function ve_call_function(cb, args,context) {
    var a1 = args[0], a2 = args[1], a3 = args[2];
    context=context||ve;
    switch (args.length) {
        case 0: return cb.call(context);
        case 1: return cb.call(context,a1);
        case 2: return cb.call(context,a1,a2);
        case 3: return cb.call(context,a1,a2,a3);
        default: return cb.apply(context, args);
    }
}
ve.add_filter=function ( $tag, $function_to_add, context, $priority) {
    $priority=$priority||10;
    context=context||this;

    var $idx = _ve_filter_build_unique_id($tag, $function_to_add, $priority);
    if(typeof ve_filter[$tag]=='undefined'){
        ve_filter[$tag]={};
    }
    if(typeof ve_filter[$tag][$priority]=='undefined'){
        ve_filter[$tag][$priority]={};
    }
    ve_filter[$tag][$priority][$idx] = {'function' : $function_to_add, 'ctx' : context};
    return true;
};
ve.apply_filters=function(tag,value){
    if(!ve_done_filter[tag]){
        ve_done_filter[tag]=1;
    }else{
        ve_done_filter[tag]++;
    }
    if(!ve_filter[tag])
        return value;
    var filters=ve.php.ksort(ve_filter[tag]);
    var k,id,filter,args;
    args=arguments;
    Array.prototype.shift.apply(args);

    for(k in filters){
        if(filters.hasOwnProperty(k)){
            for(id in filters[k]){
                if(filters[k].hasOwnProperty(id)){
                    filter=filters[k][id];
                    if(filter&&filter.function){
                        value=ve_call_function(filter.function,args,filter.ctx);
                    }
                }
            }
        }
    }
    return value;
};
ve.do_action=function(tag,arg){
    if(!ve_action[tag]){
        ve_action[tag]=1;
    }else{
        ve_action[tag]++;
    }
    if(!ve_filter[tag])
        return ;

    var filters=ve.php.ksort(ve_filter[tag]);
    var k,id,filter,args;
    args=arguments;
    Array.prototype.shift.apply(args);

    for(k in filters){
        if(filters.hasOwnProperty(k)){
            for(id in filters[k]){
                if(filters[k].hasOwnProperty(id)){
                    filter=filters[k][id];
                    if(filter&&filter.function){
                        ve_call_function(filter.function,args,filter.ctx);
                    }
                }
            }
        }
    }

};

ve.remove_filter=function( $tag, $function_to_remove, $priority ) {
    $priority=$priority || 10;
    if($function_to_remove) {
        $function_to_remove = _ve_filter_build_unique_id($tag, $function_to_remove, $priority);
        if (ve_filter[$tag] && ve_filter[$tag][$priority]) {
            delete ve_filter[$tag][$priority][$function_to_remove];
            if (_.isEmpty(ve_filter[$tag][$priority])) {
                delete ve_filter[$tag][$priority];
                if (_.isEmpty(ve_filter[$tag])) {
                    delete ve_filter[$tag];
                }
            }

        }
    }else{
        delete ve_filter[$tag];
    }
};
ve.remove_action=function(tag,function_to_remove, priority){
    ve.remove_filter(tag,function_to_remove,priority);
};
