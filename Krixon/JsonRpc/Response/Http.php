<?php
/**
 * @category   Krixon
 * @package    Krixon_JsonRpc
 */

/**
 * Krixon_JsonRpc_Response
 */
require_once 'Krixon/JsonRpc/Response.php';

/**
 * HTTP response
 *
 * @uses Krixon_JsonRpc_Response
 * @category Krixon
 * @package  Krixon_JsonRpc
 */
class Krixon_JsonRpc_Response_Http extends Krixon_JsonRpc_Response
{
    /**
     * Override __toString() to send HTTP Content-Type header
     *
     * @return string
     */
    public function __toString()
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=' . strtolower($this->getEncoding()));
        }
        return parent::__toString();
    }
}
