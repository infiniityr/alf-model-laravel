<?php
/**
 * Nom du fichier : CacheAlfresco.php
 * Projet : AlfrescoAPI
 * Date : 29/08/2017
 */

namespace Infiniityr\Alfresco\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait CacheAlfresco
{
    /**
     * storage time of the cache in minute
     * @var int
     */
    protected $store_cache_time = 24*60;

    public function saveInCache($forceSave = true)
    {
        if (!$this->getId()) {
            return false;
        }
        if (!$forceSave) {
            Cache::add($this->getId(), $this, Carbon::now()->addMinutes($this->store_cache_time));
        }
        Cache::put($this->getId(), $this, Carbon::now()->addMinutes($this->store_cache_time));
    }

    public function deleteInCache($nodeRef = null)
    {
        $nodeRef = ($nodeRef)?:$this->getId();
        if (!$nodeRef) {
            return false;
        }
        Cache::forget($nodeRef);
    }

    public function hasCache($id = null)
    {
        $id = ($id)?:$this->getId();
        if (!$id) {
            return false;
        }
        return Cache::has($id);
    }

    public function getFromCache($attributes = [])
    {
        if ($this->getId()) {
            $dataCache = static::getCache($this->getId());
            $attributes = $dataCache->getAttributes() + $attributes;
        }
        $this->fill($attributes);

        return $this;
    }

    /**
     * @param $id
     * @return static
     */
    public static function getCache($id)
    {
        return Cache::get($id, new static());
    }

    public static function removeCache($id)
    {
        return Cache::forget($id, new static());
    }
}