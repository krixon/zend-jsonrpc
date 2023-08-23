<?php

/**
 * For handling the HTTP connection to the JSON-RPC service
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Enables object chaining for calling namespaced JSON-RPC methods.
 * @see Krixon_JsonRpc_Client_ServerProxy
 */
require_once 'Krixon/JsonRpc/Client/ServerProxy.php';

/**
 * JSON-RPC Request
 * @see Krixon_JsonRpc_Request
 */
require_once 'Krixon/JsonRpc/Request.php';

/**
 * JSON-RPC Response
 * @see Krixon_JsonRpc_Response
 */
require_once 'Krixon/JsonRpc/Response.php';


/**
 * An JSON-RPC client implementation
 *
 * @category   Krixon
 * @package    Krixon_JsonRpc
 * @subpackage Client
 */
class Krixon_JsonRpc_Client
{
    /**
     * Full address of the JSON-RPC service
     * @var string
     */
    protected $_serverAddress;

    /**
     * HTTP Client to use for requests
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;

    /**
     * Request of the last method call
     * @var Krixon_JsonRpc_Request
     */
    protected $_lastRequest = null;

    /**
     * Response received from the last method call
     * @var Krixon_JsonRpc_Response
     */
    protected $_lastResponse = null;

    /**
     * Proxy object for more convenient method calls
     * @var array of Krixon_JsonRpc_Client_ServerProxy
     */
    protected $_proxyCache = [];

    /**
     * Response result type can be either an object or an array.
     * @var int
     */
    protected $_resultType = Krixon_JsonRpc_Response::TYPE_OBJECT;

    /**
     * Create a new JSON-RPC client to a remote server
     *
     * @param string $server Full address of the JSON-RPC service
     * @param Zend_Http_Client $httpClient HTTP Client to use for requests
     * @return void
     */
    public function __construct($server, Zend_Http_Client $httpClient = null)
    {
        if (null === $httpClient) {
            $this->_httpClient = new Zend_Http_Client();
        } else {
            $this->_httpClient = $httpClient;
        }

        $this->_serverAddress = $server;
    }

    /**
     * Sets the response result type.
     *
     * @param int $type
     * @return $this
     */
    public function setResultType(int $type)
    {
        $this->_resultType = $type;
        return $this;
    }

    /**
     * Sets the HTTP client object to use for connecting the JSON-RPC server.
     *
     * @param  Zend_Http_Client $httpClient
     * @return Zend_Http_Client
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        return $this->_httpClient = $httpClient;
    }

    /**
     * Gets the HTTP client object.
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }

   /**
     * The request of the last method call
     *
     * @return Krixon_JsonRpc_Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * The response received from the last method call
     *
     * @return Krixon_JsonRpc_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * Returns a proxy object for more convenient method calls
     *
     * @param string $namespace  Namespace to proxy or empty string for none
     * @return Krixon_JsonRpc_Client_ServerProxy
     */
    public function getProxy($namespace = '')
    {
        if (empty($this->_proxyCache[$namespace])) {
            $proxy = new Krixon_JsonRpc_Client_ServerProxy($this, $namespace);
            $this->_proxyCache[$namespace] = $proxy;
        }
        return $this->_proxyCache[$namespace];
    }

    /**
     * Perform a JSON-RPC request and return a response.
     *
     * @param Krixon_JsonRpc_Request $request
     * @param null|Krixon_JsonRpc_Response $response
     * @return void
     * @throws Krixon_JsonRpc_Client_HttpException
     */
    public function doRequest($request, $response = null)
    {
        $this->_lastRequest = $request;

        $http = $this->getHttpClient();
        if($http->getUri() === null) {
            $http->setUri($this->_serverAddress);
        }

        $http->setHeaders([
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
        ]);

        if ($http->getHeader('user-agent') === null) {
            $http->setHeaders(['User-Agent: Krixon_JsonRpc_Client']);
        }

        $json = (string) $this->_lastRequest;
        $http->setRawData($json);

        try {
            $httpResponse = $http->request(Zend_Http_Client::POST);
        } catch (Zend_Http_Client_Adapter_Exception $e) {
            throw new Krixon_JsonRpc_Client_Exception('Unable to connect to ' . $http->getUri() . ' Error:' . $e->getMessage());
        }

        if (!$httpResponse->isSuccessful()) {
            /**
             * Exception thrown when an HTTP error occurs
             * @see Krixon_JsonRpc_Client_HttpException
             */
            require_once 'Krixon/JsonRpc/Client/HttpException.php';
            throw new Krixon_JsonRpc_Client_HttpException(
                $httpResponse->getStatus() . ' ' . $httpResponse->getMessage(),
                $httpResponse->getStatus()
            );
        }

        if (null === $response) {
            $response = new Krixon_JsonRpc_Response($this->_lastRequest->getId());
        }
        $this->_lastResponse = $response;
        $this->_lastResponse->loadJson($httpResponse->getBody(), $this->_resultType);
    }

    /**
     * Send a JSON-RPC request to the service (for a specific method)
     *
     * @param  string $method Name of the method to call
     * @param  array $params Array of parameters for the method
     * @return mixed
     * @throws Krixon_JsonRpc_Client_FaultException
     */
    public function call($method, $params = [])
    {
        $request = $this->_createRequest($method, $params);
        $this->doRequest($request);
        return $this->_lastResponse->getReturnValue();
    }

    /**
     * Create request object
     *
     * @return Krixon_JsonRpc_Request
     */
    protected function _createRequest($method, $params)
    {
        return new Krixon_JsonRpc_Request($method, $params);
    }
}
