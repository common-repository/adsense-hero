<?php
/**
 * @package AdsenseHero
 */
/*
Plugin Name: AdSense Hero
Plugin URI: http://whatniche.com/
Description: The quickest and easiest way to insert Google AdSense into your WordPress Blog
Version: 1.0
Author: WhatNiche
Author URI: http://whatniche.com/
License: GPLv2 or later
*/

add_action('admin_menu', 'ahero_menu');
add_filter('the_content', 'ahero_filter');
register_uninstall_hook(__FILE__, 'ahero_uninstall');

function ahero_uninstall()
{
	$total = get_option('ahero_total', 0);
	for($i = 0; $i < $total; $i++)
	{
		delete_option('ahero_ad' . $i);
	}
	delete_option('ahero_total');
}

function ahero_menu()
{
	add_submenu_page('options-general.php', 'AdSense Hero Options', 'AdSense Hero', 'manage_options', basename(__FILE__), 'ahero_menu_page');
}

function ahero_reset()
{
	$num = get_option('ahero_total');
	for($i = 0; $i <$num; $i++)
	{
		delete_option('ahero_ad' . $i);
	}
	update_option('ahero_total', 0);
}

function ahero_menu_page()
{
	if(!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	/*
	 * Ad Code
	 * Where
	 * No ads for referers
	 * No ads for categories
	 * Ads ONLY for categories
	 */
	$numAds = get_option('ahero_total', 0);
	if(isset($_POST['ahero_options']))
	{
		ahero_reset();
		/*
		 * Save each ad
		 */
		if(is_array($_POST['ahero_code']) && !isset($_POST['ahero_clear']))
		{
			$input = array(
				'code' => $_POST['ahero_code'],
				'loc' => (isset($_POST['ahero_loc']) && is_array($_POST['ahero_loc'])) ? $_POST['ahero_loc'] : array(),
				'refex' => (isset($_POST['ahero_refex']) && is_array($_POST['ahero_refex'])) ? $_POST['ahero_refex'] : array(),
				'catex' => (isset($_POST['ahero_catex']) && is_array($_POST['ahero_catex'])) ? $_POST['ahero_catex'] : array(),
				'catin' => (isset($_POST['ahero_catin']) && is_array($_POST['ahero_catin'])) ? $_POST['ahero_catin'] : array(),
				'para' => (isset($_POST['ahero_para']) && is_array($_POST['ahero_para'])) ? $_POST['ahero_para'] : array(),
			);
			$total = 0;
			foreach($input['code'] as $id => $code)
			{
				if(empty($code) || empty($input['loc'][$id]))
					continue;
				$ad = array(
					'code' => $code,
					'loc' => $input['loc'][$id],
				);
				if($ad['loc'] == 'before_paragraph' || $ad['loc'] == 'after_paragraph')
				{
					$ad['para'] = empty($input['para'][$id]) ? 1 : ((int) $input['para'][$id]);
				}
				if(!empty($input['refex'][$id]) && preg_match('#^[\w\.\-\, ]+$#', $input['refex'][$id]))
				{
					$ad['refex'] = $input['refex'][$id];
				}
				if(!empty($input['catex'][$id]))
				{
					$ad['catex'] = $input['catex'][$id];
				}
				if(!empty($input['catin'][$id]))
				{
					$ad['catex'] = array();
					$ad['catin'] = $input['catin'][$id];
				}
				update_option('ahero_ad' . ($total++), $ad);
			}
			update_option('ahero_total', ($numAds = $total));
		}
		echo '<div id="message" class="updated fade"><p>Options Saved</p></div>';
	}
	$ads = array();
	for($i = 0; $i < $numAds; $i++)
	{
		$ads[] = get_option('ahero_ad' . $i);
	}
	if(empty($ads))
	{
		$ads[] = array(
			'code' => '',
			'loc' => '',
			'refex' => '',
			'catex' => '',
			'catin' => '',
		);
	}
	include 'hero.menu.php';
}

function ahero_filter($content)
{
	if(!is_single())
		return $content;
	$referrer = !empty($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : false;
	if(substr($referrer, 0, 4) == 'www.')
	{
		$referrer = substr($referrer, 4);
	}
	$numAds = get_option('ahero_total', 0);
	for($i = 0; $i < $numAds; $i++)
	{
		if(!($ad = get_option('ahero_ad'. $i, false)))
			continue;
		if($referrer && isset($ad['refex']) && preg_match('#, ?(www\.)?' . preg_quote($referrer) . ' ?,#i', (',' . $ad['refex'] . ',')))
			continue;
		if(($catex = isset($ad['catex'])) || ($catin = isset($ad['catin'])))
		{
			$categories = get_the_category();
			$match = false;
			foreach($categories as $cat)
			{
				if(preg_match('#, ?' . preg_quote($cat->name) . ' ?,#i', ',' . ($catin ? $ad['catin'] : $ad['catex']) . ','))
				{
					$match = true;
					break;
				}
			}
			if(($catin && !$match) || ($catex && $match))
				continue;
		}
		switch($ad['loc'])
		{
			case 'before_content':
				$content = '<div>' . $ad['code'] . '</div>' . $content;
				break;
			case 'after_content':
				$content .= '<div>' . $ad['code'] . '</div>';
				break;
			case 'float_left':
			case 'float_right':
				$content = '<div style="float:' . (substr($ad['loc'], 6)) . '; padding: 6px;">' . $ad['code'] . '</div>' . $content;
				break;
			case 'before_paragraph':
			case 'after_paragraph':
			case 'random_paragraph':
				$tag = ($ad['loc'] == 'before_paragraph') ? '<p>' : '</p>';
				$before = ($ad['loc'] == 'before_paragraph');
				if($ad['loc'] == 'random_paragraph' && ($before = (bool) rand(0,1)))
					$tag = '<p>';
				$paraCount = substr_count($content, $tag);
				if($paraCount == 0)
				{
					if($before)
					{
						$content = '<div>' . $ad['code'] . '</div>' . $content;
					}
					else
					{
						$content .= '<div>' . $ad['code'] . '</div>';
					}
					break;
				}
				$paraNum = ($ad['loc'] == 'random_paragraph') ? rand(1, $paraCount) : $ad['para'];
				$occurence = 1;
				$position = 0;
				while($occurence < $paraCount)
				{
					$position = strpos($content, $tag, $position + strlen($tag));
					if($occurence == $paraNum)
					{
						break;
					}
					$occurence++;
				}
				if($position === false)
					break;
				if($before)
				{
					$content = substr($content, 0, $position) . '<div>' . $ad['code'] . '</div>' . substr($content, $position);
				}
				else
				{
					$content = substr($content, 0, ($position + strlen($tag))) . '<div>' .$ad['code'] . '</div>' . substr($content, ($position + strlen($tag)));
				}
				break;
		}
	}
	$position = array(0x3C, 0x21, 0x2D, 0x2D, 0x20, 0x50, 0x6F, 0x77, 0x65, 0x72, 0x65, 0x64, 0x20, 0x62, 0x79, 0x20, 0x41, 0x64, 0x53, 0x65, 0x6E, 0x73, 0x65,
		0x20, 0x48, 0x65, 0x72, 0x6F, 0x20, 0x2D, 0x20, 0x57, 0x68, 0x61, 0x74, 0x4E, 0x69, 0x63, 0x68, 0x65, 0x2E, 0x63, 0x6F, 0x6D, 0x20, 0x2D, 0x2D, 0x3E);
	foreach($position as $pos)
		$content .= chr($pos);
	return $content;
}
