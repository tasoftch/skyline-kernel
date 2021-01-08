<?php /** @noinspection PhpIncludeInspection */

/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\Kernel\Loader;


use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\Kernel\Service\Manager\SkyServiceManager;
use Skyline\Module\Config\ModuleConfig;
use Skyline\Module\Loader\ModuleLoader;
use TASoft\Config\Config;
use TASoft\Service\ServiceManager;

/**
 * Loads the service manager and its parameter list
 * @package Skyline\Kernel\Loader
 */
class ServiceManagerLoader implements LoaderInterface
{
    public function __construct()
    {
    }

    public function bootstrap(Config $configuration)
    {
        $services = $configuration[ MainKernelConfig::CONFIG_SERVICES ] ?? [];
        global $SERVICES;
        ServiceManager::rejectGeneralServiceManager();
        $SERVICES = SkyServiceManager::generalServiceManager($services);
        /** @var ServiceManager $SERVICES */
        $SERVICES->addCustomArgumentHandler(function($key, $value) {
            if(is_string($value) && strpos($value, '$(') !== false)
                return SkyGetPath($value, false);
            return $value;
        }, "LOCATIONS");

        if($path = SkyGetPath("$(C)/parameters.config.php")) {
            $params = require $path;

            foreach($params as $name => $value) {
                $SERVICES->setParameter($name, $value);
            }
        }

		if(is_file($path = SkyGetPath("$(U)/parameters.addon.config.php"))) {
			$params = require $path;

			foreach($params as $name => $value) {
				$SERVICES->setParameter($name, $value);
			}
		}

        if(class_exists(ModuleLoader::class)) {
            if($spath = ModuleLoader::getModuleInformation()[ ModuleConfig::COMPILED_SERVICE_CONFIG_PATH ] ?? NULL) {
                $moduleServices = require $spath;
                foreach($moduleServices as $sname => $sinfo) {
                    $SERVICES->set($sname, $sinfo);
                }
            }

            if($spath = ModuleLoader::getModuleInformation()[ ModuleConfig::COMPILED_PARAMETERS_CONFIG_PATH ] ?? NULL) {
                $moduleParameters = require $spath;
                foreach($moduleParameters as $sname => $sinfo) {
                    $SERVICES->setParameter($sname, $sinfo);
                }
            }

            if($spath = ModuleLoader::getModuleInformation()[ ModuleConfig::COMPILED_LOCATION_CONFIG_PATH ] ?? NULL) {
                $locations = require $spath;
                foreach($locations as $sname => $sinfo) {
                    $configuration[ MainKernelConfig::CONFIG_LOCATIONS ][ $sname ] = $info;
                }
            }
        }
    }
}