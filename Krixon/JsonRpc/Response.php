<?php
/**
 * @category   Krixon
 * @package    Krixon_JsonRpc
 */

/**
 * Krixon_JsonRpc_Response_Exception
 */
require_once 'Krixon/JsonRpc/Response/Exception.php';

/**
 * JsonRpc Response
 *
 * Container for accessing an JSON-RPC return value and creating the JSON response.
 *
 * @category Krixon
 * @package  Krixon_JsonRpc
 */
class Krixon_JsonRpc_Response
{

    const TYPE_OBJECT = 0;
    const TYPE_ARRAY  = 1;

    /**
     * The id of the request by which to validate the response
     * @var string
     */
    protected $_id;

    /**
     * Return value
     * @var mixed
     */
    protected $_return;

    /**
     * Response character encoding
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Constructor
     *
     * Can optionally pass in the return value, otherwise, the
     * return value can be set via {@link setReturnValue()}.
     *
     * @param mixed $return
     * @param string $type
     * @return void
     */
    public function __construct($id, $return = null)
    {
        $this->setId($id);
        $this->setReturnValue($return);
    }

    /**
     * Set the id of the request
     *
     * @param string $id The id of the request used to generate this response
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Retrieve request id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set encoding to use in response
     *
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Retrieve current response encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set the return value
     *
     * Sets the return value.
     *
     * @param mixed $value
     * @return void
     */
    public function setReturnValue($value)
    {
        $this->_return = $value;
    }

    /**
     * Retrieve the return value
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Load a response from an JSON response
     *
     * Attempts to load a response from a JSON-RPC response.
     *
     * @param string $response
     * @param int $type The format of the response, either array or object
     * @return void
     */
    public function loadJson($response, $type = self::TYPE_OBJECT)
    {
        if (!is_string($response)) {
            throw new Krixon_JsonRpc_Response_Exception('Invalid JSON provided');
        }

        $type = $type == self::TYPE_ARRAY ? Zend_Json::TYPE_ARRAY : Zend_Json::TYPE_OBJECT;

        try {
            $json = Zend_Json::decode($response, $type);
        } catch (Zend_Json_Exception $e) {
            throw new Krixon_JsonRpc_Response_Exception('Failed to parse response: '.$response);
        }

        $responseId = $type == self::TYPE_ARRAY ? $json['id'] : $json->id;
        if ($this->getId() != $responseId) {
            throw new Krixon_JsonRpc_Response_Exception('Invalid response. Id does not match request id');
        }

        $this->setReturnValue($json);
    }

    /**
     * Return response as JSON
     *
     * @return string
     */
    public function saveJson()
    {
        $value = $this->getReturnValue();
        if ($value === null) {
            $value = [];
        }
        return Zend_Json::encode($value);
    }

    /**
     * Return JSON response
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveJson();
    }
}
