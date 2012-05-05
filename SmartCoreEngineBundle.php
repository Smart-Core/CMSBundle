<?php

namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SmartCoreEngineBundle extends Bundle
{
	public function boot()
	{
		require_once '_temp.php';
	}
}