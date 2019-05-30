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
 *
 * @example
 * plugins.php:
 * [
 *     ...,
 *     'myClassPlugin' => [
 *          PluginConfig::PLUGIN_CLASS      => MyClassPlugin::class,
 *          PluginConfig::PLUGIN_ARGUMENTS  => [...] same notations as in services, will be passed into constructor
 *          <events>
 *     ],
 *      'myServicePlugin' => [
 *          PluginConfig::PLUGIN_SERVICE_NAME => 'myService',
 *          <events>
 *      ]
 * ]
 *
 * <events>:
 * <Only one event>
 * [
 *      PluginConfig::PLUGIN_EVENT_NAME => 'event.name',        // Required
 *      PluginConfig::PLUGIN_PRIORITY => 0,                     // Optional, default is 0
 *      PluginConfig::PLUGIN_METHOD => 'methodToCall',          // Required
 *      PluginConfig::PLUGIN_ONCE => false,                     // Optional, default is false
 * ]
 *
 * Multiple different events
 * [
 *      PluginConfig::PLUGIN_EVENT_LISTENERS => [
 *          <Only one event>,
 *          <Only one event>,
 *          ...
 *      ]
 * ]
 *
 * Multiple different events with same globals
 * [
 *      PluginConfig::PLUGIN_EVENT_NAME => 'event.name',        // Global event name, if none set
 *      PluginConfig::PLUGIN_PRIORITY => 0,                     // Global priority if none set
 *      PluginConfig::PLUGIN_ONCE => false,                     // Global once if not overwritten
 *      PluginConfig::PLUGIN_EVENT_LISTENERS => [
 *          <Only one event>,                                   // with exception, that declared global infos are not required anymore.
 *          ...
 *      ]
 * ]
 */
abstract class PluginConfig
{
    /** @var string You should declare a service name to obtain or a direct class name of a plugin */
    const PLUGIN_SERVICE_NAME = 'service';

    /** @var string If you specify a class name, the plugin is loaded immediately and the class may subscribe itself. */
    const PLUGIN_CLASS = 'class';
    const PLUGIN_FACTORY = 'factory';
    const PLUGIN_ARGUMENTS = 'arguments';
    const PLUGIN_ARGUMENT_SOLVE_DEPENDENCIES = 'argument-dependencies'; // boolean to specify if arguments are resolved by dependency management

    // The event section
    const PLUGIN_EVENT_SECTION = 'section';
    // Can be specified to tell the plugin loader to instantiate the desired event manager
    const PLUGIN_DESIRED_EVENT_MANAGER = 'eventManager';

    // Using a service name, PLUGIN_EVENT_LISTENERS wraps a set of PLUGIN_EVENT_NAME, PLUGIN_PRIORITY and PLUGIN_METHOD.
    // Every single set will be registered as an event listener

    // Wrapper for multiple listeners on the same class/service
    const PLUGIN_EVENT_LISTENERS = 'listeners';

    const PLUGIN_EVENT_NAME = 'event';
    const PLUGIN_PRIORITY = 'priority';
    const PLUGIN_METHOD = 'method';

    //  Listen only once to the specified event
    const PLUGIN_ONCE = 'once';

    // AVAILABLE EVENT SECTIONS
    const EVENT_SECTION_BOOTSTRAP = 'bootstrap';    // All events triggered in bootstrap phase
    const EVENT_SECTION_ROUTING = 'route';          // Section to route
    const EVENT_SECTION_CONTROL = 'control';        // Section for choosing an action controller, configure it, may be apply security
    const EVENT_SECTION_RENDER = 'render';          // Section to call performAction and create a response
}