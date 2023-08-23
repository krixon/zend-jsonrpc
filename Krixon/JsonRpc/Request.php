<?php
/**
 * @category   Krixon
 * @package    Krixon_JsonRpc
 */

/**
 * Zend_Json
 */
require_once 'Zend/Json.php';

/**
 * Krixon_JsonRpc_Request_Exception
 */
require_once 'Krixon/JsonRpc/Request/Exception.php';

/**
 * JsonRpc Request object
 *
 * Encapsulates a JsonRpc request, holding the method call and all parameters.
 * Provides accessors for these, as well as the ability to load from json and to
 * create the json request string.
 *
 * @category Krixon
 * @package  Krixon_JsonRpc
 */
class Krixon_JsonRpc_Request
{
    /**
     * Request character encoding
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Method to call
     * @var string
     */
    protected $_method;

    /**
     * The id of the request
     * @var string
     */
    protected $_id;

    /**
     * Json request string
     * @var string
     */
    protected $_json;

    /**
     * Method parameters
     * @var array
     */
    protected $_params = [];

    /** @var Zend_XmlRpc_Fault */
    protected $_fault;

    /**
     * Create a new JSON-RPC request
     *
     * @param string $method (optional)
     * @param array $params  (optional)
     */
    public function __construct($method = null, $params = null, $id = null)
    {
        if (null !== $method) {
            $this->setMethod($method);
        }

        if (null !== $params) {
            $this->setParams($params);
        }

        $this->setId($id);
    }


    /**
     * Set encoding to use in request
     *
     * @param string $encoding
     * @return Krixon_JsonRpc_Request
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Retrieve current request encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set method to call
     *
     * @param string $method
     * @return boolean Returns true on success, false if method name is invalid
     */
    public function setMethod($method)
    {
        if (!is_string($method) || !preg_match('/^[a-z0-9_.:\/]+$/i', $method)) {
            $this->_fault = new Krixon_JsonRpc_Fault(634, 'Invalid method name ("' . $method . '")');
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->_method = $method;
        return true;
    }

    /**
     * Retrieve call method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Set the id of this request
     *
     * @param string|null $id An id to use for the request, or null to have an id
     *                        generated automatically
     * @return $this
     */
    public function setId($id = null)
    {
        if (null === $id) {
            $id = $this->_generateId();
        }
        $this->_id = $id;
        return $this;
    }

    /**
     * Retrieve current request id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Add a parameter to the parameter stack
     *
     * @param mixed $value
     * @return void
     */
    public function addParam($value)
    {
        $this->_params[] = $value;
    }

    /**
     * Set the parameters array
     *
     * If called with a single, array value, that array is used to set the
     * parameters stack. If called with multiple values or a single non-array
     * value, the arguments are used to set the parameters stack.
     *
     * @access public
     * @return void
     */
    public function setParams()
    {
        $argc = func_num_args();
        $argv = func_get_args();
        if (0 == $argc) {
            return;
        }

        if ((1 == $argc) && is_array($argv[0])) {
            $this->_params = $argv[0];
            return;
        }

        $this->_params = $argv;
    }

    /**
     * Retrieve the array of parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Load Json and parse into request components
     *
     * @param string $request
     * @return boolean True on success, false if an error occurred.
     */
    public function loadJson($request)
    {
        if (!is_string($request)) {
            throw new Krixon_JsonRpc_Request_Exception('Invalid JSON provided');
        }
        try {
            $json = Zend_Json::decode($request, Zend_Json::TYPE_OBJECT);
        } catch (Zend_Json_Exception $e) {
            throw new Krixon_JsonRpc_Request_Exception('Failed to parse request');
        }

        if (empty($json->method)) {
            throw new Krixon_JsonRpc_Request_Exception('Invalid request. No method passed');
        }

        $this->_method = (string) $json->method;

        // Check for parameters
        if (!empty($json->params)) {
            $this->_params = $json->params;
        }

        $this->_json = $request;

        return true;
    }

    /**
     * Create Json request
     *
     * @return string
     */
    public function saveJson()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method'  => $this->getMethod(),
            'id'      => $this->getId()
        ];
        $params = $this->getParams();
        if (!empty($params)) {
            $data['params'] = $params;
        }
        return Zend_Json::encode($data);
    }

    /**
     * Return JSON request
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveJson();
    }

    /**
     * Generate a unique id for this request
     *
     * @return string
     */
    private function _generateId()
    {
        return uniqid();
    }
}
