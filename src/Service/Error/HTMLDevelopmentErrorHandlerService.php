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


use Skyline\Kernel\Exception\SkylineKernelDetailedException;
use Throwable;

class HTMLDevelopmentErrorHandlerService extends AbstractHTTPErrorHandlerService
{
    public function handleError(string $message, int $code, $file, $line, $ctx): bool
    {
        if(error_reporting() & $code) {
            $level = "Error";

            switch (self::detectErrorLevel($code)) {
                case self::WARNING_ERROR_LEVEL:
                    $level = "Warning";
                    $bg = "#FFA";
                    $bbc = "#DD0";
                    break;
                case self::NOTICE_ERROR_LEVEL:
                    $level = "Notice";
                    $bg = "#EEE";
                    $bbc = "#888";
                    break;
                case self::DEPRECATED_ERROR_LEVEL:
                    $level = "Deprecated";
                    $bg = "#FFD";
                    $bbc = "#DD0";
                    break;
                default:
                    $bg = "#FDD";
                    $bbc = "#F00";
            }
            $code = self::detectErrorName($code);
            echo "<pre style='border: 2px solid red; background-color: $bg; padding: 1em; border-radius: 1em; border-color: $bbc'><b>$level</b> [$code]: $message at file <b>$file</b>:$line\n</pre>";
            return true;
        }

        return false;
    }

    public function handleException(Throwable $throwable): bool
    {
        if($throwable->getCode() >= 400 && $throwable->getCode() <=599)
            http_response_code( $throwable->getCode() );
        else
            http_response_code(500);
        ?>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                font: 1em normal sans-serif, monospace, "Menlo";
                border: 1px solid black;
                background-color: #ffdb86;
            }

            table caption {
                text-align: left;
                font-size: 1.5em;
                font-weight: bold;
                background-color: orange;
                border: 1px solid black;
                border-bottom: 2px solid black;
            }

            table caption span {
                display: inline-block;
                background-color: red;
                color: white;
                width: 2em;
                line-height: 2em;
                text-align: center;
            }

            table th {
                padding-right: 1em;
                text-align: right;
            }

            table .lined {
                border-bottom: 1px solid black;
            }

            table.less {
                border: none;
            }

            table.less caption {
                font-size: 1em;
                background-color: inherit;
                border: none;
                border-bottom: 1px solid black;
            }

            table.less th {
                text-align: center;
                border-bottom: 1px dashed black;
                background-color: orange;
            }

            table.less td {
                border-bottom: 1px dotted gray;
                padding-top: 0.3em;
                padding-bottom: 0.3em;
            }

            table tr.no-border {}

            table tr.no-border th, table tr.no-border td {
                border-bottom: none;
            }

            th.very-left {
                text-align: left !important;
            }

        </style>
        <table>
            <caption><span>(!)</span> Uncaught Exception <?=$throwable->getCode() ? "({$throwable->getCode()})" : ""?></caption>
            <tr>
                <th>&nbsp;</th>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th>Name:</th>
                <td><?=get_class($throwable)?></td>
            </tr>
            <tr>
                <th>Message:</th>
                <td><strong><?=$throwable->getMessage()?></strong></td>
            </tr>
            <?php
            if($throwable instanceof SkylineKernelDetailedException && ($details = $throwable->getDetails())) {
                ?>
                <tr>
                    <th> </th>
                    <td><?=$details?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <th>Location:</th>
                <td><em><?=$throwable->getFile(), ":", $throwable->getLine()?></em></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="less">
                        <br>
                        <caption>Call Stack</caption>
                        <tr>
                            <th width="30px" style="text-align: center">#</th>
                            <th width="10%" class="very-left">Time</th>
                            <th width="10%" class="very-left">Memory</th>
                            <th class="very-left">Function / Method</th>
                            <th class="very-left">Location</th>
                        </tr>
                        <?php

                        $root = realpath($_SERVER["DOCUMENT_ROOT"]);

                        $genPath = function($trace) use ($root) {
                            $path = $trace["file"] ?? "eval'd code";
                            $line = $trace["line"] ?? 0;

                            if(($pos = strpos($path, $root)) !== false) {
                                $res = substr($path, strlen($root));
                                return "<span title=\"$path\">~$res</span>:$line";
                            }

                            return "<span title=\"$path\">../" . basename($path) . "</span>:$line";
                        };


                        $genArgs = function($args) {
                            foreach($args as &$arg) {
                                if(is_object($arg))
                                    $arg = get_class($arg);
                                else
                                    $arg = gettype($arg);
                            }
                            if(count($args) == 0)
                                return "void";
                            return implode(", ", $args);
                        };

                        $genFunc = function($trace) use (&$genArgs) {

                            $func = $trace["function"];
                            if(preg_match("/^(.*?)\\\\?\\{closure\\}$/i", $func, $ms)) {
                                return sprintf("^(%s)<b>closure</b>( %s ) [%s]", $ms[1], $genArgs( $trace["args"] ?? [] ), $trace["class"] ?? "GLOBAL");
                            }

                            if(isset($trace["class"]))
                                return sprintf("%s%s<b>%s</b>( %s )", $trace["class"], $trace["type"], $trace["function"], $genArgs( $trace["args"] ?? [] ));

                            return sprintf("<b>%s</b>( %s )", $trace["function"], $genArgs($trace["args"] ?? []));
                        };


                        $genSize = function($size) {
                            $bytes = ["B", "KB", "MB", "GB"];
                            $idx = 0;

                            while ($size >= 1024) {
                                $idx++;
                                $size /= 1024;
                            }

                            return sprintf("%0.2f%s", $size, $bytes[$idx]);
                        };

                        $genTime = function($time) {
                            if(!is_numeric($time))
                                return "??";

                            $ms = $time * 1000;
                            return sprintf("+%0.1fms", $ms);
                        };

                        $timesAndSizes = [];

                        if(isset($throwable->xdebug_message) && ($pos = strpos($throwable->xdebug_message, 'Call Stack'))) {
                            $msg = substr($throwable->xdebug_message, $pos);
                            if (preg_match_all("/<td.*?>(.*?)<\/td>/i", $msg, $ms)) {
                                $addons = array_chunk($ms[1], 5);

                                foreach($addons as $addon) {
                                    if(count($addon) == 5) {
                                        array_unshift($timesAndSizes, [
                                            'time' => $addon[1],
                                            "size" => $addon[2]
                                        ]);
                                    } else {
                                        array_unshift($timesAndSizes, [
                                        ]);
                                    }
                                }
                                $addons = array_reverse($addons);
                            }
                        }
                        $theTrace = $throwable->getTrace();

                        while (count($timesAndSizes) > count($theTrace)) {
                            array_shift($timesAndSizes);
                        }

                        foreach($theTrace as $idx => $trace) {
                            $time = $timesAndSizes[$idx] ?? NULL;
                            ?>
                            <tr>
                                <td style="text-align: center"><?=count($theTrace) - $idx ?></td>
                                <td><?= isset($time['time']) ? $genTime( $time["time"] ) : "-.-" ?> </td>
                                <td><?= isset($time['size']) ? $genSize ((int)$time["size"]) : "-.-" ?> </td>
                                <td><?=$genFunc($trace)?></td>
                                <td><?=$genPath( $trace )?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr class="no-border">
                            <td style="text-align: center;">0</td>
                            <td><?=$genTime(0)?></td>
                            <td>-.-</td>
                            <td>{main}</td>
                            <td>~/<?=htmlspecialchars( basename($_SERVER["PHP_SELF"]) )?>:0</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php

        if(($prev = $throwable->getPrevious()) && $this->getConfiguration()["showNestedExceptions"] ?? false) {
            echo "<h3>Nested</h3>";
            return $this->handleException($prev);
        }

        return true;
    }
}