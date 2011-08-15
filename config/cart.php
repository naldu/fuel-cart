<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(

	'cookie_prefix' => 'fuel_',
	'cookie_suffix' => '_cart',
	'default_cart' => 'default',
	
	'default' => array(
		'expire' => 0,
		'tax' => 0.19,
		'name' => 'Cart',
		'point_sep' => '.',
		'thousands_sep' => ''
	),
	
	'carts' => array(
		'default' => array(),
	),
);
