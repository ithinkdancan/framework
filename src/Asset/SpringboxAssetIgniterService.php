<?php
namespace Springbox\Asset;

use Themosis\Core\IgniterService;

class SpringboxAssetIgniterService extends \Themosis\Asset\AssetIgniterService
{

	/**
	 * Register the AssetBuilder class.
	 *
	 * @return void
	 */
	protected function registerAssetBuilder()
	{
		$this->app->bind('springboxasset', function($app) {
			return new SpringboxAssetFactory($app['asset.finder']);
		});
	}

}
