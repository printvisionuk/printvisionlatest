define([
    'jquery'
], function ($) {
    'use strict';
    return function(config) {
       var headermoduleStatus = config.headermoduleStatus;
       var header_path = config.header_path;
       var ball_path  = config.ball_path;

       $('.headerdecorate').css({
        'background': 'url("' + header_path + '") center top repeat-x',
        'height': '66px'
        });

        $('.new_year_ball').css({
            'background': 'url("' + ball_path + '")',
            'background-repeat': 'no-repeat',
            'height': '210px'
        });

        $('#christmas_thatha_image').css({
            'position': 'fixed',
            'z-index': '9999'
        });

        if (headermoduleStatus === 1) {
            $('.page-wrapper').css('padding-top', '58px');
        }
    }
});
