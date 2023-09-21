<?php

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#app', 'url' => '/app/', 'verb' => 'GET'],
		['name' => 'page#app_get', 'url' => '/app/{path}', 'verb' => 'GET', 'requirements' => ['path' => '.+']],
		['name' => 'page#app_post', 'url' => '/app/{path}', 'verb' => 'POST', 'requirements' => ['path' => '.+']],
	]
];

