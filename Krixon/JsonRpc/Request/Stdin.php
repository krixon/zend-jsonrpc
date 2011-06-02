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
 * JsonRpc Request object - Request via STDIN
 *
 * Extends {@link Krixon_JsonRpc_Request} to accept a request via STDIN. Request is
 * built at construction time using data from STDIN; if no data is available, the
 * request is declared a fault.
 *
 * @category Zend
 * @package  Krixon_JsonRpc
 */
class Krixon_JsonRpc_Request_Stdin extends Krixon_JsonRpc_Request
{
    /**
     * Raw JSON as received via request
     * @var string
     */
    protected $_json;

    /**
     * Constructor
     *
     * Attempts to read from php://stdin to get raw POST request.
     *
     * @return void
     */
    public function __construct()
    {
        $fh = fopen('php://stdin', 'r');
        if (!$fh) {
            throw new Krixon_JsonRpc_Server_Exception('Could not read from stdin');
        }

        $json = '';
        while (!feof($fh)) {
            $json .= fgets($fh);
        }
        fclose($fh);

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
}
