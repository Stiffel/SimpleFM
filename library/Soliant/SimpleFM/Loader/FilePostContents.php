<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2016 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */
namespace Soliant\SimpleFM\Loader;

use SimpleXMLElement;
use Soliant\SimpleFM\Adapter;

class FilePostContents extends AbstractLoader
{
    /**
     * @param Adapter $adapter
     * @return SimpleXMLElement
     */
    public function load()
    {
        $this->prepare();

        libxml_use_internal_errors(true);
        $authheader = empty($this->credentials) ? '' : 'Authorization: Basic ' . base64_encode($this->credentials) . PHP_EOL;

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'User-Agent: SimpleFM' . PHP_EOL .
                    $authheader .
                    'Accept: text/xml,text/html,text/plain' . PHP_EOL .
                    'Content-type: application/x-www-form-urlencoded' . PHP_EOL .
                    'Content-length: ' . strlen($this->args) . PHP_EOL .
                    PHP_EOL,
                'content' => $this->args,
            ],
            'ssl' => [
                'verify_peer' => $this->adapter->getHostConnection()->getSslVerifyPeer(),
            ],
        ];

        /**
         * Temporarily turn off error_reporting and capture any errors for handling later
         */
        $context = stream_context_create($opts);
        $errorLevel = error_reporting();
        error_reporting(0);
        $data = file_get_contents($this->postUrl, false, $context);
        error_reporting($errorLevel);

        return $this->handleReturn($data);
    }
}
