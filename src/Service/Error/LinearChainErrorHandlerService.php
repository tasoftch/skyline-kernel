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

use Throwable;

/**
 * The chain error handler service iterates over other error handlers until one returned TRUE, so it did handle the error completely.
 *
 * @package Skyline\Kernel\Service\Error
 */
class LinearChainErrorHandlerService extends AbstractErrorHandlerService
{
    private $chain = [];

    /**
     * Should not expose chain error handler.
     */
    public static function getPurposes(): array
    {
        return [
        ];
    }

    /**
     * @inheritDoc
     */
    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        /** @var ErrorServiceInterface $service */
        foreach($this->getHandlers() as $service) {
            if($service->handleError($message, $code, $file, $line, $ctx))
                return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleException(Throwable $throwable): bool
    {
        /** @var ErrorServiceInterface $service */
        foreach($this->getHandlers() as $service) {
            if($service->handleException($throwable))
                return true;
        }
        return false;
    }

    /**
     * Pass as many services into the constructor as you want.
     * The passed arguments MUST be instances of ErrorServiceInterface or strings representing class names to be instantiated without construction arguments.
     */
    public function __construct()
    {
        $args = func_get_args();
        foreach($args as $service) {
            if(is_string($service))
                $service = new $service();

            if($service instanceof ErrorServiceInterface)
                $this->addHandler($service);
        }
    }

    /**
     * Adds an error handler
     * @param ErrorServiceInterface $handler
     */
    public function addHandler(ErrorServiceInterface $handler) {
        if(!in_array($handler, $this->chain))
            $this->chain[] = $handler;
    }

    /**
     * removes an error handler
     * @param ErrorServiceInterface $handler
     */
    public function removeHandler(ErrorServiceInterface $handler) {
        $idx = array_search($handler, $this->chain);
        if($idx !== false)
            unset($this->chain[$idx]);
    }

    /**
     * Gets the error handlers
     * @return array
     */
    public function getHandlers() {
        return $this->chain;
    }
}