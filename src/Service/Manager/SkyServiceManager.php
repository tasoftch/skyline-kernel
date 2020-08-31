<?php


namespace Skyline\Kernel\Service\Manager;


use TASoft\Service\ServiceManager;

class SkyServiceManager extends ServiceManager
{
	public function getRegisteredServicePersistentFile(): string
	{
		return SkyGetLocation("C") . DIRECTORY_SEPARATOR . "service.management.php";
	}
}