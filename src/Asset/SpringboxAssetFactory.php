<?php
namespace Springbox\Asset;

class SpringboxAssetFactory extends \Themosis\Asset\AssetFactory
{

	/**
     * Array of assets with cache id.
     *
     * @var array
     */
	protected static $cache = array();

	/**
	 * Add an asset to the application.
	 *
	 * NOTE : By default the path is relative to one of the registered
	 * paths. Make sure your asset is unique by handle and paths/url.
	 * You can also pass an external url.
	 *
	 * @param  string $handle
	 *   The asset handle name.
	 * @param  string $path
	 *   The URI to the asset or the absolute URL.
	 * @param  array|bool $deps
	 *   An array with asset dependencies or false.
	 * @param  string $version
	 *   The version of your asset.
	 * @param  bool|string $mixed
	 *   Boolean if javascript file | String if stylesheet file.
	 *
	 * @throws AssetException
	 *
	 * @return \Themosis\Asset\Asset|\WP_Error
	 */
	public function add($handle, $path, $deps = array(), $version = '1.0', $mixed = null)
	{
		// Set the version OR the cachebusted version.
		$version = (isset(static::$cache[$path])) ? static::$cache[$path] : $version;
		if (!is_string($handle) && !is_string($path)) throw new \Themosis\Asset\AssetException("Invalid parameters for [Asset::add] method.");

		$path = $this->finder->find($path);
		$args = compact('handle', 'path', 'deps', 'version', 'mixed');

		// Check if asset has an extension.
		$ext = pathinfo($path, PATHINFO_EXTENSION);

		// If extension.
		if ($ext)
		{
			// Check the type of asset.
			$type = ($ext === 'css') ? 'style' : 'script';
			return new SpringboxAsset($type, $args);
		}

		// No extension, isolated case. Return WP_Error object for info.
		return new \WP_Error('asset', __('No file extension found. Perhaps paste your asset code in your &lt;head&gt; tag.', THEMOSIS_FRAMEWORK_TEXTDOMAIN));
	}

	/**
     * Localizes scripts.
     *
     * @param  string $handle
     *   The script handle.
     * @param  string $object_name
     *   The object name used in the front-end.
     * @param  mixed $object_data
     *   The data to pass to the front-end.
     *
     * @return void
     */
	// public function localize($handle, $object_name, $object_data)
	// {
	// 	// Call wp_localize_script with the results.
	// 	wp_localize_script($handle, $object_name, $object_data);
	// }

}
