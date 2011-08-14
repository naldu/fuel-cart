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

class Cart_Basket {
	
	/**
	 * The cart's settings.
	 */
	protected $config = array();
	
	/**
	 * The cart's items.
	 */
	protected $items = array();
	
	/**
	 * Whether the cart is deleted.
	 */
	protected $deleted = false;
	
	public function __construct($config)
	{
		$this->config = $config;
		
		$items = \Cookie::get($this->config['cookie_name'], array());
		
		is_string($items) and $items = unserialize(stripslashes($items));
		
		foreach($items as $rowid => $item)
		{
			$this->items[$rowid] = new \Cart_Item($item, &$this, $rowid);
		}
	}
	
	public function delete()
	{
		\Cookie::delete($this->config['cookie_name']);
		$this->items = array();
		$this->deleted = true;
	}
	
	/**
	 * Returns a cart's config value
	 *
	 * @param	string	$key		the config key
	 * @param	mixed	$default	default value
	 */
	public function config_get($key, $default = null)
	{
		if(array_key_exists($key, $this->config))
		{
			return $this->config[$key];
		}
		return $default;
	}
	
	/**
	 * Get the cart's name
	 *
	 * @return	string		the cart name
	 */
	public function name()
	{
		return $this->config['name'];
	}
	
	/**
	 * Get a cart item
	 *
	 * @param	string	$id		the item's rowid
	 * @return	object	Cart_Item instance
	 */
	public function item($rowid)
	{
		if( ! array_key_exists($rowid, $this->items))
		{
			throw new \InvalidCartItemException('Cart item does not exist: '.$id);
		}
		return $this->items[$rowid];
	}
	
	/**
	 * Get all the items from the cart
	 * 
	 * @return array	an array of cart items
	 */
	public function items()
	{
		return $this->items;
	}
	
	/**
	 * Get the number of items in the cart
	 *
	 * @return int	number for items
	 */
	public function item_count()
	{
		return count($this->items);
	}
	
	/**
	 * Adds an items or items to the cart.
	 * When adding multiple items $options will be ignored.
	 * If the same item with the same options exists in the basket
	 * the quantity is added to the item.
	 *
	 * @param	array	$values		an item array or array of item arrays
	 * @param	array	$option		an item of item options
	 * @return	string|array	rowid or array of rowids
	 */
	public function add($values, $options = array(), $force_single = false)
	{
		if( ! $force_single and is_array(reset($values)))
		{
			$rowids = array();
			foreach($values as $value)
			{
				$rowids[] = $this->add($value, array(), true);
			}
			return $rowids;
		}
		
		$required = array('id', 'name', 'price', 'qty');
			
		foreach($required as $field)
		{
			if( ! array_key_exists($field, $values))
			{
				throw new \InvalidCartItemException('Invalid cart item, missing value: '.$field);
			}
		}
		
		$rowid = $values['id'].'::'.sha1(var_export($options, true));
		
		if(array_key_exists($rowid, $this->items))
		{
			$this->items[$rowid]->update('qty', $this->items[$id]->get_qty() + $values['qty']);
		}
		else
		{
			$item = new \Cart_Item($values, &$this, $rowid);
			count($options) > 0 and $item->set_option($options);
			$this->items[$rowid] =& $item;
		}

		return $rowid;
	}
	
	/**
	 * Remove an item from a cart.
	 *
	 * $param string	$id		the item id
	 */
	public function remove($rowid)
	{
		unset($this->items[$rowid]);
	}
	
	/**
	 * Stores the cart in a cookie, this function is called in the shutdown routine.
	 */
	public function save()
	{
		if($this->deleted)
		{
			return;
		}
		$items = array();
		foreach($this->items as $rowid => $item)
		{
			$items[$rowid] = $item->_as_array();
		}
		\Cookie::set($this->config['cookie_name'], serialize($items), $this->config['expire']);
	}
	
	/**
	 * Returns the carts total price
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function total_price($formatted = true)
	{
		$price = 0;
		foreach($this->items as $item)
		{
			$price += $item->get_subtotal(false);
		}
		
		if($formatted)
		{
			return number_format($price, 2, $this->config['point_sep'], $this->config['thousands_sep']);
		}
		return $price;
	}
	
	/**
	 * Returns the total price tax included
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function total_price_incl($formatted = true)
	{
		$price = $this->total_price(false) * (float) $this->config['tax'];
		if($formatted)
		{
			return number_format($price, 2, $this->config['point_sep'], $this->config['thousands_sep']);
		}
		return round($price, 2);
	}
	
}