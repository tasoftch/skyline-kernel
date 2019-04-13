<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Kernel\Service\Error;

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
    public function handleException(\Throwable $throwable): bool
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