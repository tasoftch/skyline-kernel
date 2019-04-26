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

namespace Skyline\Kernel\Service\Error;


use ArrayAccess;
use TASoft\Collection\PriorityCollection;

/**
 * Provide a chain error handler that can order the handlers against priority.
 *
 * @package Skyline\Kernel\Service\Error
 */
class PriorityChainErrorHandlerService extends LinearChainErrorHandlerService
{
    /** @var PriorityCollection */
    private $collection;

    /**
     * Adds an error handler with specified priority.
     * Before handling an error the handlers are ordered from lowest number to highest.
     *
     * @param ErrorServiceInterface $handler
     * @param int $priority
     */
    public function addHandler(ErrorServiceInterface $handler, int $priority = 0)
    {
        if(!$this->collection)
            $this->collection = new PriorityCollection();

        $this->collection->add($priority, $handler);
    }

    /**
     * @inheritDoc
     */
    public function removeHandler(ErrorServiceInterface $handler)
    {
        if($this->collection) {
            $this->collection->remove($handler);
        }
    }

    /**
     * @inheritDoc
     */
    public function getHandlers()
    {
        return $this->collection ? $this->collection->getOrderedElements() : [];
    }

    /**
     * PriorityChainErrorHandlerService constructor.
     * The constructor expects array arguments like: [<priority>, <error-handler>]
     */
    public function __construct()
    {
        parent::__construct();

        $args = func_get_args();
        $priority = 0;

        foreach($args as $servicePKG) {
            if(is_array($servicePKG) || $servicePKG instanceof ArrayAccess) {
                list($priority, $service) = $servicePKG;
            } else {
                $service = $servicePKG;
            }

            if(is_string($service))
                $service = new $service();

            if($service instanceof ErrorServiceInterface)
                $this->addHandler($service, $priority);
        }
    }
}