<?php
/**
 * Nom du fichier : ConnectionAPI.php
 * Projet : AlfrescoAPI
 * Date : 28/08/2017
 */

namespace Infiniityr\Alfresco\API;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ConnectionAPI implements Arrayable, Jsonable
{
    protected $curlConnection;

    protected $options = [];

    protected $processor;

    public function __construct(AlfrescoProcessor $processor, $curlConnection = null)
    {
        $this->processor = $processor;
        $this->curlConnection = ($curlConnection)?:curl_init();
    }

    public function __set($name, $value)
    {
        $curloptConst = "CURLOPT_".Str::upper($name);
        if (defined($curloptConst)) {
            curl_setopt($this->curlConnection, constant($curloptConst), $value);
            Arr::set($this->options, $curloptConst, $value);
            return true;
        }
        return true;
    }

    public function __get($name)
    {
        $opt = "CURLOPT_".Str::upper($name);
        if (Arr::has($this->options, $opt)) {
            return Arr::get($this->options, $opt);
        }
        if (defined($opt)) {
            return curl_getinfo($this->curlConnection, constant($opt));
        }

        return Arr::get(curl_getinfo($this->curlConnection), $name);
    }

    function __sleep()
    {
        return $this->toArray();
    }

    function __wakeup()
    {
        $this->curlConnection = curl_init();
    }

    function __clone()
    {
        $this->curlConnection = curl_copy_handle($this->curlConnection);
    }

    function __toString()
    {
        return $this->toJson();
    }

    function __destruct()
    {
        curl_close($this->curlConnection);
    }

    public function setAuthentication($username, $password)
    {
        if (!$this->username and !$this->userpwd) {
            $this->username = $username;
        }
        if (!$this->password and !$this->userpwd) {
            $this->password = $password;
        }
    }

    public function toArray()
    {
        return curl_getinfo($this->curlConnection);
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function getStatus()
    {
        return (int)($this->http_code)?:500;
    }

    public function getResult()
    {
        $result = curl_exec($this->curlConnection);
        return $result;
    }

    public function getError()
    {
        return curl_error($this->curlConnection);
    }

    public function run($url, $datas)
    {
        $this->url = $url;
        if (sizeof($datas) > 0)
            $this->postfields = json_encode($datas);
        return $this->getResult();
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function reload()
    {
        curl_close($this->curlConnection);
        $this->curlConnection = curl_init();
        $this->options = [];
    }
}