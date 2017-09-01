<?php

namespace Infiniityr\Alfresco\Builders;

use Closure;
use Infiniityr\Alfresco\Alfresco;
use Infiniityr\Alfresco\API\ConnectionAPI;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AlfrescoBuilder
{
    protected $connection;

    /**
     *  The current model being use
     * @var Alfresco
     */
    public $model;

    public $url;

    public $route;

    public $orders;

    public $limit;

    public $offset;

    public $wheres = [];

    public $method;

    public $whereUrl = [];

    public $columns;

    public $parser;

    public function __construct(ConnectionAPI $connectionAPI)
    {
        $this->connection = $connectionAPI;
    }

    /**
     * Set a model instance for the model being queried.
     *
     * @param  Alfresco  $model
     * @return $this
     */
    public function setModel(Alfresco $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return ConnectionAPI
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionAPI $connection
     * @return AlfrescoBuilder
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     * @return AlfrescoBuilder
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    protected function parseRoute($route = null)
    {
        $route = ($route)?:$this->getRoute();
        $url = config("alfresco.routes.$route.url", null);
        if (!$url) {
            throw new \InvalidArgumentException("Any url is find for this route : {$route}");
        }
        foreach (config("alfresco.routes.{$this->getRoute()}.url_config", []) as $name => $defaultValue) {
            $key = is_numeric($name) ? $defaultValue : $name;
            $value = Arr::get($this->whereUrl, $key, $defaultValue);
            $value = str_replace('%2F', '/',urlencode($value));
            if (Str::endsWith($url, '/'.$key)) {
                $url = Str::replaceLast('/'.$key, '/'.$value, $url);
            }
            elseif (Str::startsWith($url, $key.'/')) {
                $url = Str::replaceFirst($key.'/', $value.'/', $url);
            }
            elseif (Str::contains($url, '/'.$key.'/')) {
                $url = Str::replaceFirst('/'.$key.'/', '/'.$value.'/', $url);
            }
        }
        return $url;
    }

    protected function parseUrl($url = null)
    {
        if ($this->getRoute()) {
            $url = $this->parseRoute($this->getRoute());
        }

        if ($this->method === 'GET') {
            if (!Str::contains($url, '?')) {
                $url.='?';
            }
            $url.= http_build_query($this->getWheres());
        }
        $this->setUrl($url);
        return $this;
    }

    public function url($url = '')
    {
        (config('alfresco.routes.'.$url, false))?$this->setUrlWithRoute($url):$this->setUrl($url);
        return $this;
    }

    public function setUrlWithRoute($route)
    {
        if (!config('alfresco.routes.'.$route, false)) {
            throw new \InvalidArgumentException("The route doesn't exists {$route}");
        }
        $this->setMethod(config("alfresco.routes.$route.method"));
        $this->route = $route;

        $url = $this->parseRoute($route);

        $this->setUrl($url);
        return $this;
    }

    public function setUrl($url)
    {
        if (!Str::startsWith($url, "http://")) {
            $url = config('alfresco.config.url').':'.config('alfresco.config.port').$url;
        }
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getDatas()
    {
        if ($this->method === 'GET') {
            return [];
        }
        return $this->getWheres();
    }

    public function setMethod($method)
    {
        switch (Str::upper($method))
        {
            case 'POST':
            case 'PUT':
                $this->method = Str::upper($method);
                $this->getConnection()->$method = true;
                break;
            case 'GET':
                $this->method = 'GET';
                $this->getConnection()->httpget = true;
                break;
            case 'DELETE':
            case 'PATCH':
            $this->method = Str::upper($method);
            $this->getConnection()->customrequest = Str::upper($method);
            break;
        }
        return $this;
    }

    public function where($column, $value = null)
    {
        $this->wheres[$column] = $value;
        return $this;
    }

    public function whereInUrl($column, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $col => $value) {
                $this->whereUrl[$col] = $value;
            }
        }
        else
            $this->whereUrl[$column] = $value;
        return $this;
    }

    protected function getWheres()
    {
        return $this->wheres;
    }

    /**
     * @return
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param $parser
     * @return AlfrescoBuilder
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
        return $this;
    }

    public function parser($parser)
    {
        $this->setParser($parser);
        return $this;
    }

    protected function launchParser(Alfresco $result)
    {
        if (is_null($this->getParser())) {
            return $result;
        }
        if ($this->getParser() instanceof Closure) {
            return tap($result, $this->getParser());
        }
        if (is_array($this->getParser())) {
            return call_user_func($this->getParser(), $result);
        }
    }

    public function all(Closure $callback = null)
    {
        if (!is_null($callback)) {
            $this->setParser($callback);
        }
        return $this->get();
    }

    /**
     * @param array $columns
     * @param Closure|null $callback
     * @return Alfresco
     */
    public function get($columns = ['*'], Closure $callback = null)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        if (!is_null($callback)) {
            $this->setParser($callback);
        }

        $results = $this->getConnection()->getProcessor()->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return $this->launchParser($results);
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        $this->parseUrl($this->getUrl());

        $this->connection->returntransfer = true;

        return $this->connection->run(
            $this->getUrl(), $this->getDatas());
    }

    public function basicAuth($username = null, $password = null)
    {
        $username = ($username)?:Auth::user()->{$this->getModel()->getUsername()};
        $password = ($password)?:Auth::user()->{$this->getModel()->getPassword()};
        $this->getConnection()->setAuthentication($username, $password);

        return $this;
    }

    public function checkError($result)
    {
        if ($this->getConnection()->getStatus() !== 200) {
            throw new \Exception('Une erreur est survenue durant la récupération : '.$this->getConnection()->getStatus() . ' - '.json_encode($result));
        }
    }
}