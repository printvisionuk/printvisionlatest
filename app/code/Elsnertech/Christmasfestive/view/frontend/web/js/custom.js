define([
    'jquery'
], function ($) {
    'use strict';
    return function(config) {
       var footermoduleStatus = config.footermoduleStatus;
       var base_path = config.base_path;
       var snowman_path  = config.snowman_path;

       $('.footerdecorate').css({
        'background': 'url("' + base_path + '") center bottom repeat-x',
        'height': '184px'
        });

        $('.snow_man').css({
            'background': 'url("' + snowman_path + '")',
            'background-repeat': 'no-repeat',
            'height': '331px'
        });

        if (footermoduleStatus === 1) {
            $('.page-wrapper').css('padding-bottom', '320px');
        }
    }
});
