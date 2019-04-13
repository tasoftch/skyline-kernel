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

use Skyline\CMS\Application;
use Skyline\CMS\Service\Logging\LoggerInterface;
use TASoft\Config\Config;
use Skyline\CMS\Service\Notification\Notification;
use Skyline\CMS\Service\Notification\NotificationCenter;
use TASoft\Core\Utils\Stringify;
use TASoft\Service\ServiceManager;


/**
 * Returns the main configuration of the current running application.
 * The config is only available if the application is running!
 *
 * @return null|Config
 * @see Application::run()
 */
function SkyMainConfig(): ?Config {
    global $_MAIN_CONFIGURATION;
    return $_MAIN_CONFIGURATION;
}

function SkyMainConfigGet($key, $default = NULL) {
    $config = SkyMainConfig();
    $value = $config[$key] ?? $default;
    return ServiceManager::generalServiceManager()->map($value, true);
}

function SkyGetPath($location, $appendix = '') {
    $p = SkyMainConfig() ['locations'][$location] ?? "#";
    if($appendix)
        $p .= "/$appendix";
    return realpath($p);
}


function SkyStringifyArray(iterable $array, string $concat=', ', string $append=' and ', string $empty = "", callable $itemStringifyer = NULL) {
    $items = [];
    if(!$itemStringifyer)
        $itemStringifyer = function($value, $key) { return (string)$value; };

    foreach($array as $key => $value) {
        if(strlen($s = $itemStringifyer($value, $key)))
            $items[] = $s;
    }

    if(count($items) == 0)
        return $empty;
    if(count($items) == 1)
        return array_pop($items);
    if(count($items) == 2)
        return sprintf("%s%s%s", $items[0], $append, $items[1]);
    $last = array_pop($items);
    return sprintf("%s%s%s", implode($concat, $items), $append, $last);
}

function SkyStringifyCount(int $count, ...$args) {
    $format = "";
    $num = 0;

    foreach($args as $arg) {
        if(is_numeric($arg)) {
            if($count < $arg)
                break;

            $num = $arg * 1;
            continue;
        }

        if($count >= $num)
            $format = $arg;
    }
    return vsprintf((string)$format, $count);
}


function SkyLog(string $message, int $code = 0, ...$args) {
    $message = vsprintf($message, $args);
    /** @var ServiceManager $SERVICES */
    global $SERVICES;
    if($SERVICES->getParameter("loggingEnabled")) {
        $logger = $SERVICES->get("logger");
        if($logger instanceof LoggerInterface) {
            $logger->logMessage($message, $code);
        }
    }
}

function SkyLogError(string $message, int $code = 0, ...$args) {
    $message = vsprintf($message, $args);
    /** @var ServiceManager $SERVICES */
    global $SERVICES;
    if($SERVICES->getParameter("loggingEnabled")) {
        $logger = $SERVICES->get("logger");
        if($logger instanceof LoggerInterface) {
            $logger->logErrorMessage($message, $code);
        }
    }
}

/**
 * Sets the PDO object for Skyline CMS SkyQuery* functions.
 * Pass NULL will set the configuration default PDO.
 * After query you should set to true if you are calling SkyQuerySelect or SkyQueryInsert because those functions are generators and will be executed when iterated.
 * So if SkyQuerySetPDO() to reset default was called before the iteration, the generator will fail.
 *
 * @param PDO|NULL $PDO
 * @param bool $afterQuery
 */
function SkyQuerySetPDO(PDO $PDO = NULL, bool $afterQuery = false)
{
    global $QueryPDO, $_QueryPDO;
    if($afterQuery)
        $_QueryPDO = $PDO;
    else
        $QueryPDO = $PDO;
}

function SkyQueryGetPDO(): PDO {
    global $QueryPDO, $_QueryPDO;

    if(!$QueryPDO) {
        /** @var ServiceManager $SERVICES */
        global $SERVICES;
        return $SERVICES->get("PDO");
    }
    $pdo = $QueryPDO;
    if($_QueryPDO) {
        $QueryPDO = $_QueryPDO;
        $_QueryPDO = NULL;
    }
    return $pdo;
}


define ("SKY_QUERY_FETCH_ALL", 1);
define("SKY_QUERY_FETCH_AUTO", 4);
define("SKY_QUERY_CHECK_DATA", 2);

function SkyQuery(string $sql, array $data = NULL, int $options = 6) {
    $pdo = SkyQueryGetPDO();

    $stmt = $pdo->prepare($sql);

    if($options & SKY_QUERY_CHECK_DATA) {
        if(preg_match_all("/:([a-z0-9_]+)/i", $sql, $ms)) {
            $newData = [];
            foreach($ms[1] as $value) {
                $newData[$value] = $data[$value] ?? NULL;
            }
            $data = $newData;
        }
        if(preg_match_all("/\\?/", $sql, $ms)) {
            $newData = [];
            foreach($ms[0] as $idx => $m) {
                $newData[] = $data[$idx] ?? NULL;
            }
            $data = $newData;
        }
    }

    if(!$stmt->execute($data))
        return NULL;

    try {
        if($options & SKY_QUERY_FETCH_AUTO) {
            if($stmt->rowCount() > 1)
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            else
                return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $options & SKY_QUERY_FETCH_ALL ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        if(preg_match("/insert\s+into\s+(\w+)/i", $sql, $ms)) {
            return $pdo->lastInsertId($ms[1]);
        }
        return true;
    }
}

/**
 * @param string $sql
 * @param array|string[] ...$values
 * @return bool|Generator
 */
function SkyQuerySelect(string $sql, ...$values) {
    $pdo = SkyQueryGetPDO();

    $stmt = $pdo->prepare($sql);
    if(!$stmt->execute($values))
        return false;

    $count = 0;
    while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $count => $record;
        $count++;
    }

    return true;
}

/**
 * Prepares an SQL statement for the given fieldnames to insert values.
 * iterating over the generator you may send data to insert.
 * Sending not an array will stop insertion process.
 *
 * @param string $tableName
 * @param array $fieldNames
 * @return Generator|bool
 */
function SkyQueryInsert(string $tableName, ...$fieldNames) {
    $pdo = SkyQueryGetPDO();

    if($fieldNames) {
        $values = [];
        $fieldNames = Stringify::fromIterable($fieldNames, ", ", function($v) use (&$values) {
            $values[] = "?";
            return "`$v`";
        });
        $vs = implode(", ", $values);
        $sql = "INSERT INTO $tableName ($fieldNames) VALUES ($vs)";

        $stmt = $pdo->prepare($sql);
        while(true) {
            $vals = yield $values;
            if(!is_array($vals))
                break;
            $stmt->execute($vals);
        }
        return true;
    }
    return false;
}


function SkyMakeArray(...$args) {
    return $args;
}


function SkyPostNotificationName(string $notificationName, $object = NULL, $userInfo = NULL) {
    $not = new Notification($notificationName, $object, $userInfo);
    SkyPostNotification($not);
}

function SkyPostNotification(Notification $notification) {
    if(!NotificationCenter::hasDefaultCenter()) {
        $defCenter = NotificationCenter::getDefaultCenter();

        if($p = SkyGetPath("C", "notification-observers.php")) {
            $observers = require $p;

            foreach($observers as $notName => $classAddresses) {
                foreach($classAddresses as $classAddress) {
                    $classAddress = explode("::", $classAddress);
                    $defCenter->addObserver($classAddress, $notName);
                }
            }
        }
    }

    $defCenter = NotificationCenter::getDefaultCenter();
    $defCenter->postNotification($notification);
}