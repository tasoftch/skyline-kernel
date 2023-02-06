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

/**
 * ConfigurationFuncsTest.php
 * skyline-kernel
 *
 * Created on 2019-04-13 16:32 by thomas
 */


use PHPUnit\Framework\TestCase;
use Skyline\Kernel\Config\MainKernelConfig;
use TASoft\Config\Config;
use TASoft\Service\Config\AbstractFileConfiguration;
use TASoft\Service\ServiceManager;

require_once __DIR__ . '/../lib/CoreFunctions.php';

class ConfigurationFuncsTest extends TestCase
{
    public function testMainConfig() {
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            1, 2, 3
        ]);

        $this->assertEquals($_MAIN_CONFIGURATION, SkyMainConfig());
    }

    public function testServiceResolve() {
        ServiceManager::rejectGeneralServiceManager();
        ServiceManager::generalServiceManager([
            'myService' => [
                AbstractFileConfiguration::SERVICE_CLASS => Service1::class
            ]
        ]);

        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '$myService'
        ]);

        $this->assertInstanceOf(Service1::class, SkyMainConfigGet('PDO'));
    }

    public function testLocationResolve() {
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '$(loc)/test.php',
            MainKernelConfig::CONFIG_LOCATIONS => [
                'loc' => 'path/to/loc'
            ]
        ]);

        $this->assertEquals("path/to/loc/test.php", SkyMainConfigGet('PDO'));
    }

    public function testParameterResolve() {
        ServiceManager::rejectGeneralServiceManager();
        $sm = ServiceManager::generalServiceManager([]);

        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '%PDO%'
        ]);

        $sm->setParameter("PDO", 'My PDO');
        $this->assertEquals("My PDO", SkyMainConfigGet('PDO'));
    }

    /**
     * @expectedException PHPUnit\Framework\Error\Warning
     */
    public function testUnknownParameterResolve() {
        ServiceManager::rejectGeneralServiceManager();
        $sm = ServiceManager::generalServiceManager([]);

        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '%PDO%'
        ]);

        $sm->setParameter("PDO", 'My PDO');
        $this->assertEquals("My PDO", SkyMainConfigGet('PDO'));
    }

    public function testMultiplePathResolve() {
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '$(loc)/test.php',
            MainKernelConfig::CONFIG_LOCATIONS => [
                'loc' => '$(ROOT)/loc',
                'ROOT' => '$(PROJ)/root',
                "PROJ" => '~/here'
            ]
        ]);

        $this->assertEquals("~/here/root/loc/test.php", SkyMainConfigGet('PDO'));
    }

    /**
     * @expectedException PHPUnit\Framework\Error\Warning
     */
    public function testUnexistingPathResolve() {
        global $_MAIN_CONFIGURATION;
        $_MAIN_CONFIGURATION = new Config([
            'PDO' => '$(loc)/test.php',
            MainKernelConfig::CONFIG_LOCATIONS => [
                'loc' => '$(ROOT)/loc',
                'ROOT' => '$(PROJ)/root'
            ]
        ]);

		$this->expectException(\PHPUnit\Framework\Error\Warning::class);
        $this->assertEquals("~/here/root/loc/test.php", SkyMainConfigGet('PDO'));
    }


}

class Service1 {

}

class Service2 {

}
