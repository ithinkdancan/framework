<?php
namespace Springbox\Facades;

class SpringboxAsset extends \Themosis\Facades\Facade
{

	/**
	 * Return the igniter service key responsible for the form class.
	 * The key must be the same as the one used in the assigned
	 * igniter service.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'springboxasset';
	}

	/**
	 * Static loader to ensure that the app can register our new Asset service.
	 *
	 * @return void
	 */
	public static function load()
	{
		static::$app->register('Springbox\Asset\SpringboxAssetIgniterService');
	}

}
