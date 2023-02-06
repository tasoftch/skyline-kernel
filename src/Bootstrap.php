<?php
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

namespace Skyline\Kernel;

use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\Kernel\Config\PluginConfig;
use Skyline\Kernel\Event\BootstrapEvent;
use Skyline\Kernel\Loader\LoaderInterface;
use TASoft\Config\Config;
use Skyline\Kernel\Exception\BootstrapException;
use TASoft\Service\ServiceManager;
use TASoft\Util\PathTool;

/**
 * This class is called from skyline entry point to load the required environment.
 * To change it, see the Skyline CMS compiler and project files.
 *
 * Class Bootstrap
 * @package TASoft\Kernel
 */
class Bootstrap
{
    protected static $configuration;
    public static $skylineDirectory = 'SkylineAppData/';

    /**
     * Declare the main configuration file to boot from.
     *
     * @param string $skylineDirectory
     * @return string
     */
    public static function getConfigurationPath($skylineDirectory) {
    	static::$skylineDirectory = $skylineDirectory;
        return "$skylineDirectory/Compiled/main.config.php";
    }

    /**
     * Bootstraps Skyline CMS. This method is called from entry point to load the configuration and setup environment
     *
     * @param $compiledMainConfigurationFile
     * @param string|NULL $projectDir
     * @return Config
     */
    public static function bootstrap($compiledMainConfigurationFile, string $projectDir = NULL): Config {
        // Load configuration if needed
		if(is_array($compiledMainConfigurationFile))
			$core = $compiledMainConfigurationFile;
		elseif(is_file($compiledMainConfigurationFile))
            $core = require $compiledMainConfigurationFile;
        else
            throw new BootstrapException("Could not load Skyline CMS environment configuration", 500);

        // Apply different project directory to relative locations than current working directory
        $locations = $core[ MainKernelConfig::CONFIG_LOCATIONS ];
        if($projectDir) {
            foreach($locations as &$loc) {
            	if(!$loc)
            		continue;
                if(!PathTool::isZeroPath($loc) && strpos($loc, '$(') === false && substr($loc, 0, 2) != "./") // Do not touch absolute or excaped directory locations!
                    $loc = "$projectDir/$loc/";
            }
        }
		$core[ MainKernelConfig::CONFIG_LOCATIONS ] = array_filter($locations);

        // Expose main configuration, so the SkyMainConfig* functions have access
        $config = new Config( $core );
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = $config;

        // Iterate over loaders and bootstrap
        if($loaders = $config[ MainKernelConfig::CONFIG_LOADERS ] ?? NULL) {
            foreach($loaders as $loaderClass) {
                if($loaderClass) {
                    /** @var LoaderInterface $loaderClass */
                    $loader = new $loaderClass();
                    /** @var LoaderInterface $loader */
                    $loader->bootstrap( $config );
                }
            }
        }

        static::$configuration = $config;
        ServiceManager::generalServiceManager()->get(MainKernelConfig::SERVICE_EVENT_MANAGER)->triggerSection(PluginConfig::EVENT_SECTION_BOOTSTRAP, SKY_EVENT_BOOTSTRAP, new BootstrapEvent($config));
        return $config;
    }

    /**
     * @return Config
     */
    public static function getConfiguration()
    {
        return static::$configuration;
    }
}