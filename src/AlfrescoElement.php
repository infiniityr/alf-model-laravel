<?php
/**
 * Nom du fichier : AlfrescoElement.php
 * Projet : AlfrescoAPI
 * Date : 31/08/2017
 */

namespace Infiniityr\Alfresco;


use Infiniityr\Alfresco\Traits\CacheAlfresco;

abstract class AlfrescoElement extends Alfresco
{
    use CacheAlfresco;

    protected $primaryKey = "nodeRef";

    protected $parent;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->getFromCache($attributes);
    }

    /**
     * Get the element with his Id
     * @param string $id
     * @param string|null $parent
     * @return static
     */
    public static function get($id, $parent = null)
    {
        $model = static::getCache($id);
        if (!is_null($parent)) {
            $model->setParentId($parent);
        }
        if (empty($model->getAttributes())) {
            $model->getInformations($id);
        }
        return $model;
    }

    /**
     * Get the Id of the element
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Id of the parent element
     *
     * @param string $parent
     * @return $this
     */
    public function setParentId($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get the parent Id
     *
     * @return string|null
     */
    public function getParentId()
    {
        return $this->parent;
    }

    /**
     * @param string|null $id
     * @return static
     */
    abstract public function getInformations($id = null);

    /**
     * Parse result from the attributes fields to the others class fields
     * @param static $result
     * @return static
     */
    abstract protected function parseResultInformations($result);

    /**
     * Load data from cache, if it exists
     * @param array $attributes
     * @return $this
     */
    protected function getFromCache($attributes = [])
    {
        if ($this->getId()) {
            $dataCache = static::getCache($this->getId());
            $attributes = $dataCache->getAttributes() + $attributes;
            $this->setParentId($this->getParentId());
            $this->fill($attributes);
        }
        return $this;
    }

    public function __destruct()
    {
        $this->saveInCache();
    }

}