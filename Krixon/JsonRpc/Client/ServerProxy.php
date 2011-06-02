<?php
/**
 * @category   Krixon
 * @package    Krixon_JsonRpc
 * @subpackage Client
 */


/**
 * The namespace decorator enables object chaining to permit
 * calling JSON-RPC namespaced functions like "foo.bar.baz()"
 * as "$remote->foo->bar->baz()".
 *
 * @category   Krixon
 * @package    Krixon_JsonRpc
 * @subpackage Client
 */
class Krixon_JsonRpc_Client_ServerProxy
{
    /**
     * @var Krixon_JsonRpc_Client
     */
    private $_client = null;

    /**
     * @var string
     */
    private $_namespace = '';


    /**
     * @var array of Krixon_JsonRpc_Client_ServerProxy
     */
    private $_cache = array();


    /**
     * Class constructor
     *
     * @param string             $namespace
     * @param Krixon_JsonRpc_Client $client
     */
    public function __construct(Krixon_JsonRpc_Client $client, $namespace = '')
    {
        $this->_namespace = $namespace;
        $this->_client = $client;
    }


    /**
     * Get the next successive namespace
     *
     * @param string $name
     * @return Krixon_JsonRpc_Client_ServerProxy
     */
    public function __get($namespace)
    {
        $namespace = ltrim("$this->_namespace.$namespace", '.');
        if (!isset($this->_cache[$namespace])) {
            $this->_cache[$namespace] = new $this($this->_client, $namespace);
        }
        return $this->_cache[$namespace];
    }


    /**
     * Call a method in this namespace.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (empty($args)) {
            $args = null;
        } else {
            $args = current($args);
        }
        $method = ltrim("$this->_namespace.$method", '.');
        return $this->_client->call($method, $args);
    }
}
