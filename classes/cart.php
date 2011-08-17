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


namespace Cart;

/**
 * Exception for invalid cart instance retrieval.
 */
class InvalidCartException extends \Fuel_Exception {}

/**
 * Exception for invalid cart item insert.
 */
class InvalidCartItemException extends \Fuel_Exception {}


abstract class Cart {

	protected static $default = array();
	protected static $instances = array();
	protected static $instance = null;
	
	/**
	 * Basket factory. Returns a new Cart_Basket.
	 *
	 * @param	string	$cart	the cart identifier.
	 * @param	array	$config		aditional config array
	 * @return	object	Cart_Basket instance
	 */
	public static function factory($cart = 'default', $config = array())
	{
		$key = $cart;
		empty($config) or $key.= md5(var_export($config, true));
		
		if(array_key_exists($key, static::$instances))
		{
			return static::$instances[$key];
		}
		
		$cart_config = \Config::get('cart.carts.'.$cart);
		
		if( ! is_array($cart_config))
		{
			throw new \InvalidCartException('Could not instantiate card: '.$cart);
		}
		
		$config = $config + $cart_config;
		$config = $config + static::$default;
		
		$storage_prefix = array_key_exists('storage_prefix', $config) ? $config['storage_prefix'] : \Config::get('cart.storage_prefix', 'fuel_');
		$storage_suffix = array_key_exists('storage_suffix', $config) ? $config['storage_suffix'] : \Config::get('cart.storage_suffix', '_cart');
		
		$config['storage_key'] = $storage_prefix.$cart.$storage_suffix;
		
		$driver = '\\Cart_'.ucfirst($config['driver']);
		if( ! class_exists($driver, true))
		{
			throw new \InvalidCartException('Unknown cart driver: '.$config['driver'].' ('.$driver.')');
		}
				
		$instance = new $driver($config);
		static::$instances[$key] =& $instance;

		return static::$instances[$key];
	}
	
	/**
	 * Resturns a Cart_Basket instance
	 *
	 * @param	string	$cart		the cart identifier.
	 * @param	array	$config		aditional config array
	 * @return	object	Cart_Basket instance
	 */
	public static function instance($cart = null, $config = array())
	{
		$cart or $cart = \Config::get('cart.default_cart', 'default');
		
		$key = $cart;
		empty($config) or $key.= md5(var_export($config, true));
		
		if(array_key_exists($key, static::$instances))
		{
			return static::$instances[$key];
		}
		return static::factory($cart, $config);
	}
	
	/**
	 * Method passthrough for static usage.
	 */
	public static function __callStatic($method, $args)
	{
		static::$instance or static::$instance = static::instance();
		
		if(method_exists(static::$instance, $method))
		{
			return call_user_func_array(array(static::$instance, $method), $args);
		}
		
		throw new \BadMethodCallException('Invalid method: '.get_called_class().'::'.$method);
	}

	/**
	 * Class init.
	 */
	public static function _init()
	{
		\Config::load('cart', true);
		
		static::$default = \Config::get('cart.default');
	}
	
}