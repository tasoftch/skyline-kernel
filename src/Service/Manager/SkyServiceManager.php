<?php


namespace Skyline\Kernel\Service\Manager;

use TASoft\Service\ServiceManager;

class SkyServiceManager extends ServiceManager
{
	public function getRegisteredServicePersistentFile(): string
	{
		return SkyGetPath("$(U)/service.management.php");
	}
}