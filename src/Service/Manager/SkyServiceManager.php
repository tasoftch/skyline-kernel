<?php


namespace Skyline\Kernel\Service\Manager;


use Skyline\Kernel\Bootstrap;
use TASoft\Service\ServiceManager;

class SkyServiceManager extends ServiceManager
{
	public function getRegisteredServicePersistentFile(): string
	{
		return Bootstrap::$skylineDirectory . "service.management.php";
	}
}