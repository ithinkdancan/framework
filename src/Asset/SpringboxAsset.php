<?php
namespace Springbox\Asset;

class SpringboxAsset extends \Themosis\Asset\Asset
{

	/**
	 * Localizes script assets.
	 *
	 * @param  string $object_name
	 *   The object name passed to the script.
	 * @param  mixed $data
	 *   The data to localize.
	 *
	 * @return void
	 */
	public function localize($object_name = '', $data)
	{
		// Make sure we're a script that we're trying to localize.
		if ($this->type === 'script')
		{
			// Make sure the script is registered.
			$footer = (is_bool($this->args['mixed'])) ? $this->args['mixed'] : false;
			wp_register_script($this->key, $this->args['path'], $this->args['deps'], $this->args['version'], $footer);

			// Call the localization of the script.
			wp_localize_script($this->key, $object_name, $data);

			// Enqueue the newly localized script.
			static::registerScript($this);
		}
	}

}
