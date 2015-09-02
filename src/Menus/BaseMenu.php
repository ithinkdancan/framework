<?php
namespace Springbox\Menus;

use \Walker_Nav_Menu;

class BaseMenu extends Walker_Nav_Menu
{

	/**
	 * The menu slug.
	 * @var string
	 */
	public static $menu_slug = '';

	/**
	 * The menu classes.
	 * @var array
	 */
	public static $menu_classes = array();

	/**
	 * Helper function that returns a rendered version of this menu.
	 *
	 * @return string
	 */
	public static function render()
	{
		return wp_nav_menu(array(
			'menu'       => static::$menu_slug,
			'walker'     => new static(),
			'menu_class' => join(' ', static::$menu_classes),
		));
	}

	/**
	 * Helper function to generate an attribute string from a key/value array.
	 *
	 * @param array $attributes
	 *   The array of key/value pairs.
	 *
	 * @return string
	 */
	public function getAttributes($attributes = array())
	{
		// Build up the output string.
		$output = '';
		foreach ($attributes as $attr => $value)
		{
			if (!empty($value))
			{
				$value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
				$output .= ' ' . $attr . '="' . $value . '"';
			}
		}

		return $output;
	}

}
