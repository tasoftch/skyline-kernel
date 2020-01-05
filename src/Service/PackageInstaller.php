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

namespace Skyline\Kernel\Service;



use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\InstallationManager;
use Composer\Installer\PackageEvent;
use Composer\Package\CompletePackage;
use Skyline\Kernel\Config\MainKernelConfig;
use TASoft\Config\Config;
use TASoft\Service\ServiceManager;
use TASoft\Util\PDO;
use Exception;

class PackageInstaller
{
    public static $skylineConfigurationFile = 'SkylineAppData/Compiled/main.config.php';
    public static $skylineParametersFile = 'SkylineAppData/Compiled/parameters.config.php';

    public static function getServiceManager(): ?ServiceManager {
        static $serviceManager = NULL;
        if(!$serviceManager) {
            if(is_file(self::$skylineConfigurationFile)) {
                $config = new Config( require self::$skylineConfigurationFile );
                ServiceManager::rejectGeneralServiceManager();
                $serviceManager = ServiceManager::generalServiceManager( $config[ MainKernelConfig::CONFIG_SERVICES ] );
            }

            if($serviceManager && is_file(self::$skylineParametersFile)) {
                $parameters = require self::$skylineParametersFile;
                foreach($parameters as $name => $parameter)
                    $serviceManager->setParameter($name, $parameter);
            }
        }
        return $serviceManager;
    }

    /**
     * Checks, if a table exists in the used data base.
     * If not, tries to execute SQL for creation.
     *
     * @param string $tableName     The required table name
     * @param array $creationSQL
     * @return bool
     */
    public static function checkPDOTable(string $tableName, array $creationSQL): bool {
        static $PDO;
        if(!$PDO) {
            $PDO = static::getServiceManager()->get( "PDO" );
        }

        if($PDO instanceof PDO) {
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $driver = $PDO->getAttribute(PDO::ATTR_DRIVER_NAME);

            try {
                $PDO->exec("SELECT TRUE FROM $tableName LIMIT 1");
                echo "$tableName exists.\n";
                return true;
            } catch (Exception $exception) {
                if($sql = $creationSQL[ $driver ] ?? $creationSQL[0] ?? NULL) {
                    $PDO->exec($sql);
                    echo "$tableName created\n";
                    return true;
                } else
                    trigger_error("Could not create SQL $tableName table for driver $driver", E_USER_WARNING);
            }
        }
        return false;
    }

    public static function getInstallationDirectory(PackageEvent $event) {
        /** @var InstallOperation $op */
        $op = $event->getOperation();
        /** @var CompletePackage $pkg */
        $pkg = $op->getPackage();
        /** @var Composer $composer */
        $composer = $event->getComposer();
        /** @var InstallationManager $installationManager */
        $installationManager = $composer->getInstallationManager();
        return $installationManager->getInstallPath($pkg);
    }

    public static function install(PackageEvent $event) {
        $path = self::getInstallationDirectory($event);

        if(is_file( "$path/install.php" )) {
            require "$path/install.php";
        }
    }

    public static function uninstall(PackageEvent $event) {
        $path = self::getInstallationDirectory($event);

        if(is_file( "$path/uninstall.php" )) {
            require "$path/uninstall.php";
        }
    }
}