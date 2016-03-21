<?php

return [

	/*
	|--------------------------------------------------------------------------
	| AhsayOBS server credentials
	|--------------------------------------------------------------------------
	|
	| Credentials of an admin user of the AhsayOBS server.
	|
	*/

	/**
	* Configure client constructor parameters. Example: base_uri, handler, ....
	*/
	'defaults' => [
		/**
		* Configures a base URL for the client so that requests created using a relative URL are combined with the base_url
		* See: http://guzzle.readthedocs.org/en/latest/quickstart.html#creating-a-client
		*/
		'base_uri' => 'http://ahsay.youserver.com' . '/?',
		// 'LoginName='.$username.'&Password='.$password   ,
		'uri' => '/obs/api/',
	],
	/**
	* Options to add after the endpoint
	*/

//	'merge' => ['server_admin_username' => 'admin'],

	'merge' => [
		'LoginName' => '',
		'Password' => '',
	],
	/**
	* Define your endpoints
	*
	* Example endpoint:
	* 'endpoint' => [
	*		'uri' => '',
	*		'options' => []
	* ]
	*/
	'endpoints' => [
		'Authenticate' => [
			'uri' => 'AuthUser.do',
			'options' => []
		]

		'Get user' => [
			'uri' => 'GetUser.do?',
			'options' => []
		]

		'Get storage statistics for a particular user' => [
			'uri' => 'GetUserStorageStat.do?',
			'options' => []
		]

	]
	// Authenticate a user against OBS'


];
