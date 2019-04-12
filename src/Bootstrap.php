<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Kernel;

use Symfony\Component\HttpFoundation\Request;
use TASoft\Config\Config;
use TASoft\Config\Transformer\RegexCallback;
use TASoft\Kernel\Exception\BootstrapException;

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

        /** @var FileLocationsTransformer $controller */
        $controller = FileLocationsTransformer::getController();
        $trans = new RegexCallback("/\\$\(([^\)]+)\)/i", function($ms) use ($locations) {
            return $locations[ $ms[1] ] ?? $ms[0];
        });
        $controller->addTransformer($trans);

        $config = new Config( $core );
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = $config;


        if($php_sess_domain = $config["PHP_SESSION"]["includeAllSubDomains"] ?? false) {
            $host = explode(".", $request->getHost());
            $host[0] = "";
            ini_set("session.cookie_domain", implode(".", $host));
        }


        if($loaders = $config['loaders'] ?? NULL) {
            foreach($loaders as $loaderClass) {
                /** @var LoaderInterface $loaderClass */
                $loader = new $loaderClass();
                $loader->bootstrap( $config , $request);
            }
        }

        static::$configuration = $config;

        SkyPostNotificationName(SKYLINE_DID_BOOTSTRAP_NOTIFICATION);

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