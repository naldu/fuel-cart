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

class Cart_Session extends \Cart_Driver {
	
	/**
	 * Returns the datastring.
	 *
	 * @param	string	$key		storage key
	 * @return	string|array		datastring or empty array of not found
	 */
	protected function _get($key)
	{
		return \Session::get($key, array());
	}
	
	/**
	 * Stores the data.
	 *
	 * @param	string	$key			storage key
	 * @param	string	$data_string	serialized data string
	 */
	protected function _set($key, $data_string)
	{
		\Session::set($key, $data_string, $this->config['cookie_expire']);
	}
	
	/**
	 * Deletes the data.
	 *
	 * @param	string	$key		storage key
	 */
	protected function _delete($key)
	{
		\Session::delete($key);
	}
	
}