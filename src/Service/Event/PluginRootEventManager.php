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

namespace Skyline\Kernel\Service\Event;


use Skyline\Kernel\Config\PluginConfig;
use TASoft\Collection\AbstractCollection;
use TASoft\DI\DependencyManager;
use TASoft\EventManager\Event\EventInterface;
use TASoft\EventManager\EventManager;
use TASoft\EventManager\SectionEventManager;
use TASoft\Service\ServiceManager;

class PluginRootEventManager extends SectionEventManager
{
    private $pluginsFile;
    private $plugins;

    private $serviceManager;

    private function _loadPlugins() {
        if(NULL === $this->plugins) {
            $this->plugins = require $this->pluginsFile;

            $sectionManagers = [];

            foreach($this->plugins as $plugin) {
                $manager = $this;

                if($section = $plugin[ PluginConfig::PLUGIN_EVENT_SECTION ] ?? NULL) {
                    if(!isset($sectionManagers[$section])) {
                        $sectionManagers[$section] = $m = new EventManager();
                        $this->addSectionEventManager($section, $m);
                    }
                    $manager = $sectionManagers[$section];
                }

                $globalEventName = $plugin[ PluginConfig::PLUGIN_EVENT_NAME ] ?? NULL;
                $globalPriority = $plugin[ PluginConfig::PLUGIN_PRIORITY ] ?? 0;
                $globalOnce = $plugin[ PluginConfig::PLUGIN_ONCE ] ?? false;

                $wrapper = $plugin[ PluginConfig::PLUGIN_EVENT_LISTENERS ] ?? [];
                if(!$wrapper) {
                    $wrapper = [ [PluginConfig::PLUGIN_METHOD => $plugin[ PluginConfig::PLUGIN_METHOD ] ?? NULL] ];
                }

                foreach($wrapper as $wrap) {
                    $eventName = $wrap[ PluginConfig::PLUGIN_EVENT_NAME ] ?? $globalEventName;
                    if(!$eventName) { $eMessage = "No event name found for subscription" ; goto failure; }
                    $method = $wrap[ PluginConfig::PLUGIN_METHOD ] ?? NULL;
                    if(!$method) { $eMessage = "No method declared for event subscription $eventName" ; goto failure; }

                    $priority = $wrap[ PluginConfig::PLUGIN_PRIORITY ] ?? $globalPriority;
                    $once = $wrap[ PluginConfig::PLUGIN_PRIORITY ] ?? $globalOnce;

                    if($service = $plugin[ PluginConfig::PLUGIN_SERVICE_NAME ] ?? NULL) {
                        $cb = $this->_createPluginServiceCallback($service, $method);
                    } elseif($class = $plugin[ PluginConfig::PLUGIN_CLASS ] ?? NULL) {
                        $arguments = $plugin[ PluginConfig::PLUGIN_ARGUMENTS ] ?? [];
                        $cb = $this->_createPluginClassCallback($class, $method, $arguments);
                    } else {
                        $eMessage = "Can not create instantiation of subscription on $eventName";
                        goto failure;
                    }

                    if($once)
                        $manager->addOnce($eventName, $cb, $priority);
                    else
                        $manager->addListener($eventName, $cb, $priority);
                }

                continue;
                failure:
                trigger_error($eMessage, E_USER_WARNING);
            }
        }
    }

    /**
     * @param $class
     * @param $method
     * @param $arguments
     * @param DependencyManager $dependencyManager
     * @return callable
     */
    private function _createPluginClassCallback($class, $method, $arguments) {
        return function(...$args) use ($arguments, $method, $class) {
            static $instance = NULL;
            if(!$instance) {
                if($arguments) {
                    $arguments = $this->serviceManager->mapArray( AbstractCollection::makeArray( $arguments ));
                    $instance = new $class(...array_values($arguments));
                } else {
                    $instance = new $class();
                }
            }

            return call_user_func([$instance, $method], ...$args);
        };
    }

    /**
     * @param $serviceName
     * @param $method
     * @return callable
     */
    private function _createPluginServiceCallback($serviceName, $method) {
        return function(...$args) use ($serviceName, $method) {
            return call_user_func([$this->serviceManager->get($serviceName), $method], ...$args);
        };
    }

    /**
     * PluginEventManager constructor.
     * @param $pluginsFile
     */
    public function __construct($pluginsFile, ServiceManager $serviceManager)
    {
        $this->pluginsFile = $pluginsFile;
        $this->serviceManager = $serviceManager;
    }

    public function trigger(string $eventName, EventInterface $event = NULL, ...$arguments): EventInterface
    {
        $this->_loadPlugins();
        return parent::trigger($eventName, $event, $arguments);
    }
}