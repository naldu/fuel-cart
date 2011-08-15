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
				
		$storage_driver = '\\'.ucfirst($this->config['storage']);
				
		$items = $storage_driver::get($this->config['storage_key'], array());
		
		is_string($items) and $items = unserialize(stripslashes($items));
		
		foreach($items as $rowid => $item)
		{
			$this->items[$rowid] = new \Cart_Item($item, &$this, $rowid);
		}
	}
	
	public function delete()
	{
		$storage_driver = '\\'.ucfirst($this->config['storage']);
		$storage_driver::delete($this->config['storage_key']);
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
	 * Check whether the cart holds a specific item
	 *
	 * @param	string	$rowid		the item's rowid
	 * @return	bool	whether the item is in the cart
	 */
	public function has_item($rowid)
	{
		return array_key_exists($rowid, $this->items);
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
	public function num_items()
	{
		return count($this->items);
	}
	
	/**
	 * Adds an items or items to the cart.
	 * When adding multiple items $options will be ignored.
	 * If the same item with the same options exists in the basket
	 * the quantity is added to the item.
	 *
	 * @param	array	$values			an item array or array of item arrays
	 * @param	array	$option			an item of item options
	 * @param	bool	$force_single	force single item insert
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
		
		$required = array('id', 'name', 'price');
			
		foreach($required as $field)
		{
			if( ! array_key_exists($field, $values))
			{
				throw new \InvalidCartItemException('Invalid cart item, missing value: '.$field);
			}
		}
		
		array_key_exists('qty', $values) or $values['qty'] = 1;
		
		$rowid = $values['id'].'::'.sha1(var_export($options, true));
		
		if(array_key_exists($rowid, $this->items))
		{
			$this->items[$rowid]->update('qty', $this->items[$id]->get_qty() + $values['qty']);
		}
		else
		{
			$values['__itemoptions'] = $options;
			$item = new \Cart_Item($values, &$this, $rowid);
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
	 * Stores the cart, this function is called in the shutdown routine.
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
		
		$storage_driver = '\\'.ucfirst($this->config['storage']);
		$storage_driver::set($this->config['storage_key'], serialize($items), $this->config['cookie_expire']);
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
	
	/**
	 * Returns quantity total
	 *
	 * @return	int		the quantity total.
	 */
	 public function total_qty()
	 {
	 	$total = 0;
	 	foreach($this->items as $item)
	 	{
	 		$total += $item->get_qty();
	 	}
	 	return $total;
	 }
	
	/**
	 * Updates an items rowid.
	 *
	 * @param	string	$rowid		item's rowid
	 * @return	string	the new rowid
	 */
	public function _update_rowid($rowid)
	{
		$item = $this->items[$rowid];
		$new_rowid = $item->get_id().'::'.sha1(var_export($item->get_options(), true));
		if($rowid !== $new_rowid)
		{
			$this->items[$new_rowid] = $item;
			unset($this->items[$rowid]);
		}
		return $new_rowid;
	}
	
}