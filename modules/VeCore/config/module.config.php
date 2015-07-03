<?php
return array(
    'view_manager'=>array(
        'template_map'=>array(
            'abcd'=>'123',
        ),
    ),
    'resources'=>array(
        'ElementsInit'=>array(
            'js'=>array(
                //array('init',__DIR__.'/../view/js/init.js',false,VE_VERSION,true),

                ),
        ),
    ),
    'elements'=>array(
        'VeCore'=>array(
            'VeRow',
            'VeCol',
            'VeText',
            'VeImage',
            'VeButton',
            'VeQuote',
            'VeOrderList',
            'VeUnOrderList',
            'VeWpRss',
            'VeWpPages',
            'VeWpCalendar',
           // 'VeTextWithHeader',
            'VeVideo',
            'VeSlider',

            'VeWpArchives',

            'VeWpCategories',
            'VeWpLinks',
            'VeWpMeta',
            'VeWpNavMenu',

            'VeWpRecentComments',
            'VeWpRecentPosts',

            'VeWpSearch',
            'VeWpTagCloud',
            'VeCustom',
        ),
    ),
    'features'=>array(
        'VeCore'=>array(
            'CssEditor',
            'CssAdvanced',
        )
    ),
);