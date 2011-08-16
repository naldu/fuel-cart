<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Frank de Jonge <info@frenky.net>
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_core_namespace('Cart');

Autoloader::add_classes(array(
	'Cart\\Cart'						=> __DIR__.'/classes/cart.php',
	'Cart\\InvalidCartException'		=> __DIR__.'/classes/cart.php',
	'Cart\\InvalidCartItemException'	=> __DIR__.'/classes/cart.php',
	'Cart\\Cart_Item'					=> __DIR__.'/classes/cart/item.php',
	'Cart\\Cart_Driver'					=> __DIR__.'/classes/cart/driver.php',
	'Cart\\Cart_Cookie'					=> __DIR__.'/classes/cart/cookie.php',
	'Cart\\Cart_Session'				=> __DIR__.'/classes/cart/session.php',
));