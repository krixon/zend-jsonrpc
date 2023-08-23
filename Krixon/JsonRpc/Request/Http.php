<?php
/**
 * @category   Krixon
 * @package    Krixon_JsonRpc
 */

/**
 * Krixon_JsonRpc_Request
 */
require_once 'Krixon/JsonRpc/Request.php';

/**
 * JsonRpc Request object - Request via HTTP
 *
 * Extends {@link Krixon_JsonRpc_Request} to accept a request via HTTP. Request is
 * built at construction time using a raw POST; if no data is available, the
 * request is declared a fault.
 *
 * @category Zend
 * @package  Krixon_JsonRpc
 */
class Krixon_JsonRpc_Request_Http extends Krixon_JsonRpc_Request
{
    /**
     * Array of headers
     * @var array
     */
    protected $_headers;

    /**
     * Raw JSON as received via request
     * @var string
     */
    protected $_json;

    /**
     * Constructor
     *
     * Attempts to read from php://input to get raw POST request; if an error
     * occurs in doing so, or if the JSON is invalid, the request is declared a
     * fault.
     *
     * @return void
     */
    public function __construct()
    {
        $json = @file_get_contents('php://input');
        if (!$json) {
            throw new Krixon_JsonRpc_Exception('Unable to read HTTP POST data');
            return;
        }

        $this->_json = $json;

        $this->loadJson($json);
    }

    /**
     * Retrieve the raw JSON request
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->_json;
    }

    /**
     * Get headers
     *
     * Gets all headers as key => value pairs and returns them.
     *
     * @return array
     */
    public function getHeaders()
    {
        if (null === $this->_headers) {
            $this->_headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $this->_headers[$header] = $value;
                }
            }
        }

        return $this->_headers;
    }

    /**
     * Retrieve the full HTTP request, including headers and JSON
     *
     * @return string
     */
    public function getFullRequest()
    {
        $request = '';
        foreach ($this->getHeaders() as $key => $value) {
            $request .= $key . ': ' . $value . "\n";
        }

        $request .= $this->_json;

        return $request;
    }
}
