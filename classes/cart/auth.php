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
 
/*

Needed database schema:

CREATE TABLE `carts` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `user_id` int(255) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

*/

namespace Cart;

class Cart_Auth extends \Cart_Driver {

	/**
	 * Get the user's id.
	 *
	 * @return	mixed	the user's id
	 */
	protected function _user_id()
	{
		if(array_key_exists('impersonate', $this->config))
		{
			return $this->config['impersonate'];
		}
		$user_id = \Auth::instance()->get_user_id();
		return $user_id[1];
	}
	
	/**
	 * Returns the datastring.
	 *
	 * @param	string	$key		storage key
	 * @return	string|array		datastring or empty array of not found
	 */
	protected function _get($key)
	{
		$user_id = $this->_user_id();
		
		if($user_id === 0)
		{
			return \Cookie::get($key, array());
		}
		
		$cart = \DB::select()
			->from($this->config_get('cart_table', 'carts'))
			->as_assoc()
			->where('identifier', $key)
			->and_where('user_id', $user_id)
			->execute();
		
		$cookie_fallback = \Cookie::get($key, false);
		
		$return = (count($cart) > 0) ? $cart[0]['contents'] : array();
		
		if($cookie_fallback !== false)
		{
			\Cookie::delete($key);
			
			if(empty($return))
			{
				return $cookie_fallback;
			}
			
			is_array($return) or $return = unserialize(stripslashes($return));
			$cookie_fallback = unserialize(stripslashes($cookie_fallback));
			$return = array_merge($return, $cookie_fallback);
		}

		return $return;
	}
	
	/**
	 * Stores the data.
	 *
	 * @param	string	$key			storage key
	 * @param	string	$data_string	serialized data string
	 */
	protected function _set($key, $data_string)
	{
		$user_id = $this->_user_id();
		
		if($user_id === 0)
		{
			return \Cookie::set($key, $data_string, $this->config['cookie_expire']);
		}
					
		$count = (int) \DB::select(\DB::expr('COUNT(*) as count'))
			->from($this->config_get('cart_table', 'carts'))
			->where('identifier', $key)
			->and_where('user_id', $user_id)
			->as_object()
			->execute()
			->current()
			->count;

		if($count < 1)
		{
			\DB::insert($this->config_get('cart_table', 'carts'))
				->set(array(
					'identifier' => $key,
					'user_id' => $this->_user_id(),
					'contents' => $data_string
				))
				->execute();
		}
		else
		{
			\DB::update($this->config_get('cart_table', 'carts'))
				->value('contents', $data_string)
				->where('identifier', '=', $key)
				->and_where('user_id', $user_id)
				->execute();
		}
	}
	
	/**
	 * Deletes the data.
	 *
	 * @param	string	$key		storage key
	 */
	protected function _delete($key)
	{
		$user_id = $this->_user_id();;
		
		if($user_id === 0)
		{
			return \Cookie::delete($key);
		}

		\DB::delete($this->config_get('cart_table', 'carts'))
			->where('identifier', '=', $key)
			->and_where('user_id', $user_id)
			->execute();
	}
	
}