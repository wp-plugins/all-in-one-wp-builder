<?php

return array(
    'modules'=>array(
        'VeCore',
    ),
    'view_manager'=>array(
        'template_map'=>array(

        ),
        'template_base_dir'=>__DIR__.'/../view/templates',
        'template_ext'=>'phtml',
    ),
    'resources'=>array(
        'reset'=>array(
            'css'=>array(
                array('reset',__DIR__.'/../view/css/reset.css'), //__DIR__.'/../view/css/editor/fa.css'
            ),
        ),
        'bootstraps'=>array(
            'css'=>array(
                array('bootstrap',__DIR__.'/../view/css/bootstrap.min.css'),
                array('bootstrap-theme',__DIR__.'/../view/css/bootstrap-theme.min.css'),
            ),
            'js'=>array(
                array('bootstrap',__DIR__.'/../view/js/bootstrap.min.js','jquery'),
                array('ve_front',__DIR__.'/../view/js/ve_front.js',array('jquery','underscore')),

            ),
        ),
        'front'=>array(
            'frontJs'=>array(
                //array('front',__DIR__.'/../view/js/ve_front.js','jquery'),
            ),
            'frontCss'=>array(
                array('font-awesome',__DIR__.'/../view/css/editor/fa/css/font-awesome.min.css')
            )
        ),
        'editor'=>array(
            'eCss'=>array(
                //array('bootstrap',__DIR__.'/../view/css/bootstrap.min.css'),
                //array('bootstrap-theme',__DIR__.'/../view/css/bootstrap-theme.min.css'),
                array('ve_jquery_ui',__DIR__.'/../view/libraries/jquery-ui/jquery-ui.css'),
                array('select2',__DIR__.'/../view/libraries/select2/css/select2.min.css',array(),VE_VERSION),
                array('editor',__DIR__.'/../view/css/editor/editor.css'),
                array('font-awesome',__DIR__.'/../view/css/editor/fa/css/font-awesome.min.css'),
                array('tooltipster',__DIR__.'/../view/css/tooltipster.css'),
                array('setting_menu',__DIR__.'/../view/css/setting_menu.css'),

            ),
            'eJs'=>array(
                array('select2',__DIR__.'/../view/libraries/select2/js/select2.min.js',array(),VE_VERSION),
                array('ve_phpjs',__DIR__.'/../view/libraries/phpjs/phpjs.js',array(),VE_VERSION),
                array('ve_jqueryserializeobject',__DIR__.'/../view/libraries/jquery.serialize-object.js',array(),VE_VERSION),
                array('tooltipster',__DIR__.'/../view/js/jquery.tooltipster.min.js','jquery'),
                array('ve_define',__DIR__.'/../view/js/editor/ve_define.js',
                    array(
                        'jquery',
                        'underscore',
                        'backbone',
                        'jquery-ui-draggable',
                        'jquery-ui-droppable',
                        'jquery-ui-dialog',
                        'jquery-ui-tabs',
                        //'ve_phpjs',
                    ),VE_VERSION,true),

                array('ve',__DIR__.'/../view/js/editor/ve.js',array('ve_define'),VE_VERSION,true),
                array('ve_command_controls',__DIR__.'/../view/js/editor/command_controls.js',array('ve_define'),VE_VERSION,true),
                array('ve_action_and_filter',__DIR__.'/../view/js/editor/default-actions-filters.js',array('ve_define'),VE_VERSION,true),
                array('ve_elements',__DIR__.'/../view/js/editor/elements.js',array('ve_define'),VE_VERSION,true),
                array('ve_elements_views',__DIR__.'/../view/js/editor/elements_views.js',array('ve_define'),VE_VERSION,true),
                array('ve_editor',__DIR__.'/../view/js/editor/editor.js',array('ve_define'),VE_VERSION,true),
                array('media_editor',__DIR__.'/../view/js/editor/media-editor.js',array('ve_define'),VE_VERSION,true),
                array('ve_editor_views',__DIR__.'/../view/js/editor/editor_views.js',array('ve_define'),VE_VERSION,true),
                array('ve_panel_views',__DIR__.'/../view/js/editor/panel.js',array('ve_define'),VE_VERSION,true),
                array('ve_custom_css',__DIR__.'/../view/js/editor/custom_css.js',array('ve_define'),VE_VERSION,true),
                array('ve_load',__DIR__.'/../view/js/editor/load.js',array('ve_define'),VE_VERSION,true),
            ),
            'fCss'=>array(
                array('ve_iframe',__DIR__.'/../view/css/editor/iframe.css'),
                array('ve_jquery_ui',__DIR__.'/../view/libraries/jquery-ui/jquery-ui.css'),
                array('ve_menu_context',__DIR__.'/../view/libraries/context-menu/src/jquery.contextMenu.css'),
                array('font-awesome', __DIR__.'/../view/css/editor/fa/css/font-awesome.min.css' ),

            ),
            'fJs'=>array(
                array('ve_menu_context',__DIR__.'/../view/libraries/context-menu/src/jquery.contextMenu.js',array('jquery-ui-position'),VE_VERSION,true),
                array('ve_iframe',__DIR__.'/../view/js/editor/iframe.js',array(
                    'jquery-ui-draggable',
                    'jquery-ui-droppable',
                    'jquery-ui-sortable',
                    'jquery-ui-resizable'),VE_VERSION,true),
            ),
        ),

        'page'=>array(
            'js'=>array(),
            'css'=>array(
                array('ve-style',__DIR__.'/../view/css/style.css'),
            ),
        )
    )
);