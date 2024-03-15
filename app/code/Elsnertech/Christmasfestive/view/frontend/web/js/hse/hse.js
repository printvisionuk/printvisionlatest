/*
 * Velan Info Services India Pvt Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.velanapps.com/License.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * Velan Info Services does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Velan Info Services does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   velanapps
 * @package    Holiday Season Effects
 * @author     Velan Team 
 * @copyright  Copyright (c) 2012 - 2013 Velan Info Services India Pvt Ltd. (http://www.velanapps.com)
 * @license    http://store.velanapps.com/License.txt
 */

/* -------------------Cloud Effects-------------- */

var cloud_ani_speed = 3000;
function start_cloud_animation(){
	jQuery('#cloud_effect_disp_content').fadeOut(0);
	var set_initial_position = 275;
	jQuery('#left_cloud').css('left',set_initial_position);
	jQuery('#right_bottom_cloud').css('right',set_initial_position+50);
	jQuery('#right_cloud').css('right',set_initial_position);
	jQuery('#moon').css('left',set_initial_position);
	run_cloud_animation();
}
var val = 0;
function run_cloud_animation(){
	jQuery('#left_cloud').animate({ left:val,}, cloud_ani_speed, function(e){});
	jQuery('#right_cloud').animate({ right:val,}, cloud_ani_speed+2000, function(e){});
	jQuery('#right_bottom_cloud').animate({ right:val,}, cloud_ani_speed+2500, function(e){show_cloud_animation_offer();});
	jQuery('#moon').animate({ left:540 ,}, cloud_ani_speed+3100, function(e){});	
	jQuery('#cloud_effect_disp_content').fadeIn(3000);
}

function show_cloud_animation_offer(){
	jQuery('#moon').animate({ left:505,top:-5 ,}, 2500, function(e){});
	jQuery('#cloud_effect_offer').animate({ 'top':10,}, 100, function(e){
		jQuery('#cloud_effect_disp_content').css('color','#eee');
		jQuery('#cloud_effect_disp_content').animate({ 'top':80,}, 100, function(e){
			jQuery('#cloud_effect_disp_content').animate({ 'top':60,}, 150, function(e){
				jQuery('#cloud_effect_disp_content').animate({ 'top':65,}, 150, function(e){
					jQuery('#cloud_effect_disp_content').animate({ 'top':60,}, 200, function(e){
						jQuery('#cloud_effect_offer').animate({ 'top':0,}, 100, function(e){
							jQuery('#cloud_effect_offer').animate({ 'top':60,}, 100, function(e){
								jQuery('#cloud_effect_disp_content').animate({ 'top':300,}, 100, function(e){});
								jQuery('#cloud_effect_offer').animate({ 'top':40,}, 200, function(e){});
							});
						});
					});				
				});
			});
		})
	});
}

function close_cloud_view(){jQuery('#cloud_total_total_view').fadeOut(1000);jQuery('#total_cloud_view').fadeOut(1000);}


/* ------------------------Tree Effects------------------------- */
function set_tree_width_height(new_width,new_height,pos,img){
	jQuery('#set_christmas_tree').css('max-width',new_width).css('max-height',new_height).addClass(pos);
	jQuery('#set_christmas_tree img').attr('src',img);
}
jQuery('#set_christmas_tree').fadeOut(0).fadeIn(600);


/*---------Santa Claus----------*/
var right_to_left_image = '';
var left_to_right_image = '';
function thatha_fly(type,new_speed,fly_image,next_image){
	if(new_speed){ani_speed = new_speed;}
	if(fly_image){right_to_left_image = fly_image;}
	if(next_image){left_to_right_image = next_image;}
	
	if(type == 'random'){
		jQuery('#christmas_thatha_image img').attr('src',fly_image);
		thatha_random_animation();
	} else {
		jQuery('#christmas_thatha_image img').attr('src',next_image);
		animate_vertical_horizontal(type);
	}
}

function animate_vertical_horizontal(flip_style){
	jQuery('#christmas_thatha_image').css('bottom',0).css('right',-jQuery('#christmas_thatha_image').width());
	var pos = jQuery(document).width();
	var thatha_image_width = jQuery('#christmas_thatha_image').width();

	if(flip_style == 'flip'){
	
		var bottom_pos = jQuery(document).height()/4.5;
				
		jQuery('#christmas_thatha_image').animate({ right:pos,bottom:(bottom_pos*1)}, ani_speed, function(e){
			set_flying_image(right_to_left_image);
			jQuery('#christmas_thatha_image').animate({ right:-(thatha_image_width),bottom:(bottom_pos*2)}, ani_speed, function(e){
				set_flying_image(left_to_right_image);
				jQuery('#christmas_thatha_image').animate({ right:pos,bottom:(bottom_pos*3)}, ani_speed, function(e){
					set_flying_image(right_to_left_image);
					jQuery('#christmas_thatha_image').animate({ right:-(thatha_image_width),bottom:(bottom_pos*4)}, ani_speed, function(e){
						set_flying_image(left_to_right_image);
						animate_vertical_horizontal(flip_style);
					});
				});
			});
		});
		
	}else{
	
		var bottom_pos = jQuery(document).height()/3.5;
				
		jQuery('#christmas_thatha_image').animate({ right:pos,bottom:(bottom_pos*1)}, ani_speed, function(e){
			set_flying_image(right_to_left_image);
			jQuery('#christmas_thatha_image').css('bottom',jQuery('#christmas_thatha_image').css('bottom')).css('right',-jQuery('#christmas_thatha_image').width());
			jQuery('#christmas_thatha_image').animate({ right:pos,bottom:(bottom_pos*2)}, ani_speed, function(e){
				set_flying_image(left_to_right_image);
				jQuery('#christmas_thatha_image').css('bottom',jQuery('#christmas_thatha_image').css('bottom')).css('right',-jQuery('#christmas_thatha_image').width());
				jQuery('#christmas_thatha_image').animate({ right:pos,bottom:(bottom_pos*3)}, ani_speed, function(e){
					set_flying_image(left_to_right_image);
					animate_vertical_horizontal(flip_style);
				});
			});
		});
		
	}
}

function set_flying_image(get_image){
	jQuery('#christmas_thatha_image img').attr('src',get_image);
}

function thatha_random_animation(){
	var h = jQuery(document).height() - 50;
    var w = jQuery(document).width() - 50;
    var nh = Math.floor(Math.random() * h);
    var nw = Math.floor(Math.random() * w);
    jQuery('#christmas_thatha_image').animate({ top: nh, left: nw },5000, function(){
      thatha_random_animation();        
    });
};


