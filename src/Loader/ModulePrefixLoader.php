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

namespace Skyline\Kernel\Loader;


use Composer\Autoload\ClassLoader;
use Skyline\Kernel\FileConfig;
use Symfony\Component\HttpFoundation\Request;
use TASoft\Config\Config;

/**
 * The module prefix loader tries to load the precompiled modules and  their root directory to PHP controllers
 *
 * @package Skyline\Kernel\Loader
 */
class ModulePrefixLoader implements LoaderInterface
{
    public static $precompiledModuleData = [];

    /** @var ClassLoader */
    private static $autoloader;

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(Config $configuration, ?Request $request)
    {
        $path = SkyGetPath("$(C)/modules.config.php");

        if(!$path)
            trigger_error("Module linker could not load precompiled modules.", E_USER_WARNING);
        else {
            static::$precompiledModuleData = require $path;

            if($prefixes = static::$precompiledModuleData[ 'prefixes' ] ?? false) {
                self::$autoloader = new ClassLoader();
                self::$autoloader->register();

                foreach($prefixes as $prefix => $paths) {
                    if(defined("SKY_DEBUG") && SKY_DEBUG) {
                        // compiled as debug, the paths are already absolute.
                    } else {
                        $paths = array_map(function($path) {
                            return getcwd() . "/$path";
                        }, $paths);
                    }

                    self::$autoloader->addPsr4($prefix, $paths);
                }
            }
        }
    }

    /**
     * @return ClassLoader|null
     */
    public static function getAutoloader(): ?ClassLoader
    {
        return self::$autoloader;
    }
}