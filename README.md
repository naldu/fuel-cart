Cart package for Fuel
=====================

Introduction
------------

The Cart package handles one or multiple carts.

Configuration
-------------

Copy config/cart.php to app/config/cart.php and change whatever setting in need of changing.

Single Cart Usage
-----------------

Add an item to the cart:
	Cart::add(array(
		'name' => 'Blue Ball',
		'id' => 'toys_2937',
		'qty' => 1,
		'price' => 6.99,
	));


Multi Cart Usage
----------------

Get an cart instance:
	$cart = Cart::instance();

Add an item to the cart, with options:
	$cart::add(array(
		'name' => 'Blue Ball',
		'id' => 'toys_2937',
		'qty' => 1,
		'price' => 6.99,
	), array(
		'wrapping' => 'Giftwrap',
		'extra' => array('Ball pump', 2.99), // add an option with an added price
	));