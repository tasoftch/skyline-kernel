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

namespace Skyline\Kernel\Config;

/**
 * Class PluginConfig is used to define array keys in the plugins.php file
 * @package Skyline\Kernel\Config
 */
abstract class PluginConfig
{
    /** @var string You should declare a service name to obtain or a direct class name of a plugin */
    const PLUGIN_SERVICE_NAME = 'service';

    /** @var string If you specify a class name, the plugin is loaded immediately and the class may subscribe itself. */
    const PLUGIN_CLASS = 'class';
    const PLUGIN_ARGUMENTS = 'arguments';

    // Using a service name, PLUGIN_EVENT_LISTENERS wraps a set of PLUGIN_EVENT_NAME, PLUGIN_PRIORITY and PLUGIN_METHOD.
    // Every single set will be registered as an event listener

    /** @var string  */
    const PLUGIN_EVENT_LISTENERS = 'listeners';

    const PLUGIN_EVENT_NAME = 'event';
    const PLUGIN_PRIORITY = 'priority';
    const PLUGIN_METHOD = 'method';

    const PLUGIN_ONCE = 'once';
}