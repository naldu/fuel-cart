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

class Cart_Item {

	/**
	 * Item values
	 */
	protected $values = array();
	
	/**
	 * Item values
	 */
	protected $options = array();
	
	/**
	 * Cart
	 */
	protected $cart;
	
	/**
	 * Cart rowid
	 */
	protected $rowid;

	/**
	 * Constructor.
	 *
	 * @param	array	$values		an array of item values
	 * @param	object	$cart		the cart it resides in
	 * @param	array	$rowid		cart rowid
	 */
	public function __construct($values, $cart, $rowid)
	{
		$this->cart = $cart;
		$this->rowid = $rowid;
		$this->values = $values;
		if(array_key_exists('__itemoptions', $this->values))
		{
			$this->options = $this->values['__itemoptions'];
			unset($this->values['__itemoptions']);
		}
	}
	
	/**
	 * Returns the item's name
	 *
	 * @return	string	name of the item
	 */
	public function get_name()
	{
		return $this->values['name'];
	}
	
	/**
	 * Returns the item's id
	 *
	 * @return	string	the id
	 */
	public function get_id()
	{
		return $this->values['id'];
	}
	
	/**
	 * Returns the item's quantity
	 *
	 * @return	int	the quantity
	 */
	public function get_qty()
	{
		return (int) $this->values['qty'];
	}
	
	/**
	 * Returns the items total price
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function get_price($formatted = true)
	{
		$price = (float) $this->values['price'];
		
		foreach($this->options as $option)
		{
			$price += $option[2];
		}
		
		if($formatted)
		{
			return number_format($price, 2, $this->cart->config_get('point_sep'), $this->cart->config_get('thousands_sep'));
		}		
		return $price;
	}
	
	/**
	 * Returns the carts subtotal
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function get_subtotal($formatted = true)
	{
		$subtotal =  $this->get_qty()*$this->get_price();
		
		if($formatted)
		{
			return number_format($subtotal, 2, $this->cart->config_get('point_sep'), $this->cart->config_get('thousands_sep'));
		}	
		return $subtotal;
	}
	
	/**
	 * Sets an option for a cart item.
	 *
	 * @param	string	$key	the option key
	 * @param	mixed	$value	the option vale
	 * @param	float	$price	the added price
	 */
	public function set_option($key, $value = null, $price = 0)
	{
		is_array($key) or $key = array($key => array($value, $price));
		
		foreach($key as $_key => $value)
		{
			is_array($value) or $value = array($value, 0);
			count($value) < 2 and $value[] = 0;
			$this->options[$_key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Deletes an option from a cart item.
	 *
	 * @param	string	$key	the option key
	 */
	public function delete_option($key)
	{
		unset($this->options[$key]);
		
		return $this;
	}
	
	/**
	 * Check if the item has options set.
	 *
	 * @return	bool	whether the item has options
	 */
	public function has_options()
	{
		return (bool) count($this->options);
	}
	
	/**
	 * Check if the item has a specific options set.
	 *
	 * @return	bool	whether the item has options
	 */
	public function has_option($key)
	{
		return array_key_exists($key, $this->options);
	}
	
	/**
	 * Updates an item
	 *
	 * @param	string|array	$key	key or array or values to update array(key => value)
	 * @param	mixed			$value	the new value
	 */
	public function update($key, $value = null)
	{
		is_array($key) or $key = array($key => $value);
		
		foreach($key as $_key => $value)
		{
			$this->values[$_key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Removed itself from the cart.
	 */
	public function delete()
	{
		$this->cart->remove($this->rowid);
	}
	
	/**
	 * Item's array, used for cart saving.
	 */
	public function _as_array()
	{
		$return = $this->values;
		$return['__itemoptions'] = $this->options;
		return $return;
	}
	
}