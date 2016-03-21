<?php namespace Connexeon\Ahsay;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use MyVendor\MyPackage\Handlers\Package;

class AhsayServiceProvider extends ServiceProvider
{
	/**
	 * @var bool $defer Indicates if loading of the provider is deferred.
	 */
	protected $defer = false;

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot()
	{

		$this->publishes([
			__DIR__.'/../config/ahsay.php' => config_path('ahsay.php')
		], 'config');

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/../config/ahsay.php', 'ahsay'
		);

		$this->registerAliases();

		$this->registerServices();

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string
	 */
	public function provides()
	{
		return ['ahsay'];
	}

	/**
	 * Register the AppKit aliases / facades.
	 *
	 * @return void
	 */
	protected function registerAliases() {

		$aliases = AliasLoader::getInstance();
        $aliases->alias(
            'ahsay',
            'Connexeon\Ahsay\Facades\AhsayFacade'
        );
	}

	/**
	 * Register the package services.
	 *
	 * @return void
	 */
	protected function registerServices()
	{

		$this->app->bindShared('ahsay', function ($app) {
			return new Package($app['config']);
		});
	}

}
