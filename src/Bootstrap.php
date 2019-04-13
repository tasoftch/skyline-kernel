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

use Skyline\Kernel\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use TASoft\Config\Config;
use TASoft\Config\Transformer\RegexCallback;
use Skyline\Kernel\Exception\BootstrapException;

/**
 * This class is called from skyline entry point to load the required environment.
 * To change it, see the skyline-cms compiler and project files.
 *
 * Class Bootstrap
 * @package TASoft\Kernel
 */
class Bootstrap
{
    protected static $configuration;

    public static $development = false;

    public static function getConfigurationPath($skylineDirectory, Request $request) {
        return "$skylineDirectory/Compiled/main.config.php";
    }

    public static function bootstrap($compiledMainConfigurationFile, string $projectDir = NULL, Request $request = NULL): Config {
        if(is_file($compiledMainConfigurationFile))
            $core = require $compiledMainConfigurationFile;
        elseif(is_array($compiledMainConfigurationFile))
            $core = $compiledMainConfigurationFile;
        else
            throw new BootstrapException("Could not load Skyline CMS environment configuration", 500);

        $locations = $core['locations'];

        if($projectDir) {
            foreach($locations as &$loc) {
                $loc = "$projectDir/$loc";
            }

            $core['locations'] = $locations;
        }

        $config = new Config( $core );
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = $config;

        if($loaders = $config['loaders'] ?? NULL) {
            foreach($loaders as $loaderClass) {
                /** @var LoaderInterface $loaderClass */
                $loader = new $loaderClass();
                /** @var LoaderInterface $loader */
                $loader->bootstrap( $config , $request);
            }
        }

        static::$configuration = $config;
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