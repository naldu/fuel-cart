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
			foreach($this->values['__itemoptions'] as $option)
			{
				is_array($option) or $option = array($option, 0);
				$this->option[] = $option;
			}
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
	 * Returns the item's tax rate.
	 *
	 * @return	float	the items's tax rate.
	 */
	public function get_tax()
	{
		return array_key_exists('tax', $this->values) ? $this->values['tax'] : $this->cart->tax();
	}
	
	/**
	 * Returns the item's options
	 *
	 * @return	array	an array of item options
	 */
	public function get_options()
	{
		return $this->options;
	}
	
	/**
	 * Returns the item's rowid
	 *
	 * @return	string	the item's rowid
	 */
	public function get_rowid()
	{
		return $this->rowid;
	}
	
	/**
	 * Returns the items total price
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function get_price($formatted = true, $include_tax = false)
	{
		$price = (float) $this->values['price'];
		
		$include_tax and $price = $this->_price_tax($price);
		
		foreach($this->options as $option)
		{
			$price += $option[2];
		}
				
		if($formatted)
		{
			return number_format($price, 2, $this->cart->config_get('dec_point'), $this->cart->config_get('thousands_sep'));
		}		
		return $price;
	}
	
	/**
	 * Calculate the tax on a per item level.
	 * 
	 * @param	float	$price		the items price
	 * @return	float	the price including tax
	 */
	protected function _price_tax($price)
	{
		$tax = $this->get_tax();
		
		is_array($tax) or $tax = array($tax);
				
		foreach($tax as $_tax)
		{
			if(is_string($_tax) and substr($_tax, 0, 1) === '+')
			{
				$price += (float) substr($_tax, 1);
			}
			else
			{
				$price += (float) $price * $_tax;
			}
		}
		
		return round($price, 2);
	}
	
	/**
	 * Returns the carts subtotal
	 *
	 * @param	bool	$formatted		whether to format the returned price
	 * @return	float|string	the price
	 */
	public function get_subtotal($formatted = true, $include_tax = false)
	{
		$subtotal = $this->get_price(false, $include_tax) * $this->get_qty();
		
		if($formatted)
		{
			return number_format($subtotal, 2, $this->cart->config_get('dec_point'), $this->cart->config_get('thousands_sep'));
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
		
		$this->rowid = $this->cart->_update_rowid($this->rowid);
		
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
		$this->rowid = $this->cart->_update_rowid($this->rowid);
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