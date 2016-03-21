<?php
namespace Connexeon\Ahsay;

use Illuminate\Config\Repository;

use GuzzleHttp\Client;

class AhsayResource  extends Client
{
	/**
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param  \Illuminate\Config\Repository     $config
	 */
	public function __construct(Repository $config)
	{
		$this->config = $config;
	}
}
