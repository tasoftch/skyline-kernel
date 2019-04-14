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

use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\Kernel\Loader\ConstantsLoader;
use Skyline\Kernel\Loader\FunctionLibraryLoader;
use Skyline\Kernel\Loader\ServiceManagerLoader;
use Skyline\Kernel\Loader\StaticErrorHandler;
use Skyline\Kernel\Service\DI\DependencyInjectionContainer;
use Skyline\Kernel\Service\DI\EventManagerContainer;
use Skyline\Kernel\Service\Error\DisplayErrorHandlerService;
use Skyline\Kernel\Service\Error\LogErrorHandlerService;
use Skyline\Kernel\Service\Error\PriorityChainErrorHandlerService;
use TASoft\Service\Config\AbstractFileConfiguration;

// For safety reasons the kernel configuration is designed for production.
// You should change every debug or test configuration in your project source.

return [
    // Nest debugging and testing into parameters

    MainKernelConfig::CONFIG_DEBUG => false,
    MainKernelConfig::CONFIG_TEST => false,

    // Read Skyline CMS Version
    MainKernelConfig::CONFIG_VERSION => MainKernelConfig::getSkylineVersion(),

    // Specify some core locations
    MainKernelConfig::CONFIG_LOCATIONS => [
        // The Skyline Kernel core library
        'kernel-lib' => 'vendor/skyline/lib',

        // The precompiled
        'C' => '$(/)/Compiled',
        'L' => '$(/)/Logs',

        // Your project MUST declare the root Skyline App Data path if different to default
        '/' => 'SkylineAppData',
    ],

    // Define the core loaders. They can be overwritten by a project config or api config or what else.
    // You should not change the oder they are loaded!
    MainKernelConfig::CONFIG_LOADERS => [
        'errors' => StaticErrorHandler::class,
        'constants' => ConstantsLoader::class,
        "functions" => FunctionLibraryLoader::class,
        "services" => ServiceManagerLoader::class
    ],

    // Kernel services
    MainKernelConfig::CONFIG_SERVICES => [
        MainKernelConfig::SERVICE_DEPENDENCY_MANAGER => [
            AbstractFileConfiguration::SERVICE_CONTAINER => DependencyInjectionContainer::class
        ],
        MainKernelConfig::SERVICE_EVENT_MANAGER => [
            AbstractFileConfiguration::SERVICE_CONTAINER => EventManagerContainer::class,
            AbstractFileConfiguration::SERVICE_INIT_CONFIGURATION => [
                'pluginFile' => '$(C)/plugins.php'
            ]
        ],
        MainKernelConfig::SERVICE_ERROR_CONTROLLER => [
            AbstractFileConfiguration::SERVICE_CLASS => PriorityChainErrorHandlerService::class,
            AbstractFileConfiguration::SERVICE_INIT_ARGUMENTS => [
                [-2, '$logErrorHandler'],
                [-1, '$displayErrorHandler']
            ]
        ],
        'displayErrorHandler' => [
            AbstractFileConfiguration::SERVICE_CLASS => DisplayErrorHandlerService::class
        ],
        "logErrorHandler" => [
            AbstractFileConfiguration::SERVICE_CLASS => LogErrorHandlerService::class,
            AbstractFileConfiguration::SERVICE_INIT_ARGUMENTS => [
                'file' => '$(L)/'
            ]
        ]
    ]
];
