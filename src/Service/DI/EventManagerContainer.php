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

namespace Skyline\Kernel\Service\DI;

use Skyline\Kernel\Config\MainKernelConfig;
use Skyline\Kernel\Config\PluginConfig;
use Skyline\Kernel\Exception\SkylineKernelDetailedException;
use TASoft\Collection\AbstractCollection;
use TASoft\DI\DependencyManager;
use TASoft\EventManager\EventSubscriberInterface;
use TASoft\EventManager\SubscribableEventManager;
use TASoft\Service\ConfigurableServiceInterface;
use TASoft\Service\Container\AbstractContainer;
use TASoft\Service\ServiceManager;
use TASoft\Service\StaticConstructorServiceInterface;

class EventManagerContainer extends AbstractContainer implements StaticConstructorServiceInterface, ConfigurableServiceInterface, EventSubscriberInterface
{
    private $configuration;
    private $serviceManager;

    private static $plugins;

    protected function loadInstance()
    {
        $this->instance = $eventManager = new SubscribableEventManager();
        $path = SkyGetPath($this->configuration["pluginFile"] ?? NULL, false);
        if(!is_file($path)) {
            $e = new SkylineKernelDetailedException("Plugin Path Error");
            $e->setDetails("Can not load plugins from configuration path %s", SkyDisplayPath($path));
            throw $e;
        }

        self::$plugins = require $path;

        $eventManager->addSubscriberHandler(function($subscription, SubscribableEventManager $eventManager) {
            if(is_array($subscription)) {
                $eMessage = "";

                $globalEventName = $subscription[ PluginConfig::PLUGIN_EVENT_NAME ] ?? NULL;
                $globalPriority = $subscription[ PluginConfig::PLUGIN_PRIORITY ] ?? 0;
                $globalOnce = $subscription[ PluginConfig::PLUGIN_ONCE ] ?? false;

                $wrapper = $subscription[ PluginConfig::PLUGIN_EVENT_LISTENERS ] ?? [];
                if(!$wrapper) {
                    $wrapper = [ [PluginConfig::PLUGIN_METHOD => $subscription[ PluginConfig::PLUGIN_METHOD ] ?? NULL] ];
                }

                foreach($wrapper as $wrap) {
                    $eventName = $wrap[ PluginConfig::PLUGIN_EVENT_NAME ] ?? $globalEventName;
                    if(!$eventName) { $eMessage = "No event name found for subscription" ; goto failure; }
                    $method = $wrap[ PluginConfig::PLUGIN_METHOD ] ?? NULL;
                    if(!$method) { $eMessage = "No method declared for event subscription $eventName" ; goto failure; }

                    $priority = $wrap[ PluginConfig::PLUGIN_PRIORITY ] ?? $globalPriority;
                    $once = $wrap[ PluginConfig::PLUGIN_PRIORITY ] ?? $globalOnce;

                    if($service = $subscription[ PluginConfig::PLUGIN_SERVICE_NAME ] ?? NULL) {
                        $cb = $this->_createPluginServiceCallback($service, $method);
                    } elseif($class = $subscription[ PluginConfig::PLUGIN_CLASS ] ?? NULL) {
                        $arguments = $subscription[ PluginConfig::PLUGIN_ARGUMENTS ] ?? [];
                        $cb = $this->_createPluginClassCallback($class, $method, $arguments);
                    } else {
                        $eMessage = "Can not create instantiation of subscription on $eventName";
                        goto failure;
                    }

                    if($once)
                        $eventManager->addOnce($eventName, $cb, $priority);
                    else
                        $eventManager->addListener($eventName, $cb, $priority);
                }

                return true;
                failure:
                trigger_error($eMessage, E_USER_WARNING);
            }
            return false;
        });

        $eventManager->subscribeClass(static::class);
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

    public function __construct($arguments = NULL, ServiceManager $serviceManager = NULL)
    {
        $this->serviceManager = $serviceManager;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public static function getEventListeners(): array
    {
        return self::$plugins;
    }
}