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
 * BootstrapTest.php
 * skyline-kernel
 *
 * Created on 2019-04-14 09:56 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Kernel\Bootstrap;
use TASoft\Service\ServiceManager;

class BootstrapTest extends TestCase
{
    private $time;
    protected function setUp()
    {
        parent::setUp();
        $this->time = microtime(true);
    }

    protected function tearDown()
    {
        $diff = microtime(true) - $this->time;
        printf("%.1fms", $diff*1000);
        parent::tearDown();
    }

    public function testBootstrap() {
        ServiceManager::rejectGeneralServiceManager();
        Bootstrap::bootstrap('../lib/kernel.config.php');

        /** @var ServiceManager $SERVICES */
        global $SERVICES;

        $this->assertFalse( $SERVICES->getParameter("CMS.Debug") );
        $this->assertFalse( $SERVICES->getParameter("CMS.Test") );

        $this->assertFalse(SKY_DEBUG);
        $this->assertFalse(SKY_TEST);
    }
}
