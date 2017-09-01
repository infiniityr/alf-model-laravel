<?php
/**
 * Nom du fichier : ConnectionResolver.php
 * Projet : AlfrescoAPI
 * Date : 28/08/2017
 */

namespace Infiniityr\Alfresco\API;

use Illuminate\Database\ConnectionResolverInterface;

class ConnectionResolver implements ConnectionResolverInterface
{

    protected $app;
    /**
     * All of the registered connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new connection resolver instance.
     *
     * @param $app
     * @param ConnectionAPI $connection
     * @internal param array $connections
     */
    public function __construct($app, ConnectionAPI $connection)
    {
        $this->app = $app;
        $this->addConnection(ConnectionAPI::class, $connection);
        $this->setDefaultConnection(ConnectionAPI::class);
    }

    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        return $this->connections[$name];
    }

    /**
     * Add a connection to the resolver.
     *
     * @param  string  $name
     * @param  ConnectionAPI  $connection
     * @return void
     */
    public function addConnection($name, ConnectionAPI $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }

}