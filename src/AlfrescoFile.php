<?php
/**
 * Nom du fichier : AlfrescoFile.php
 * Projet : AlfrescoAPI
 * Date : 29/08/2017
 */

namespace Infiniityr\Alfresco;


use Infiniityr\Alfresco\Builders\AlfrescoBuilder;
use Infiniityr\Alfresco\Builders\AlfrescoBuilderFile;
use Illuminate\Support\Str;

class AlfrescoFile extends AlfrescoElement
{
    protected $metadatas = [];

    protected $type;

    protected $permissions = [];

    public function getInformations($nodeRef = null)
    {
        $nodeRef = ($nodeRef)?:$this->getId();
        if (!$nodeRef) {
            return false;
        }
        if ($this->hasCache($nodeRef)) {
            $this->getFromCache($this->getAttributes(), $this->getMetadatas());
            if (!empty($this->getAttributes())) {
                return $this;
            }
        }
        $query = static::getMetadataWithNodeRef($nodeRef);

        $model = $query->get();
        if ($model instanceof AlfrescoFile and $model->getMetadatas()) {
            foreach ($model->getMetadatas() as $metadata => $value) {
                $this->addMetadata($metadata, $value);
            }
            foreach ($model->getAttributes() as $attribute => $value) {
                $this->setAttribute($attribute, $value);
            }
            $this->setType($model->getType());
        }

        $query = static::getInformationWithNodeRef($nodeRef);
        $model = $query->get();
        if ($model instanceof AlfrescoFile and $model->hasPermission()) {
            $this->setPermissions($model->getPermissions());
            foreach ($model->getAttributes() as $attribute => $value) {
                $this->setAttribute($attribute, $value);
            }
        }

        $this->saveInCache();
        return $this;
    }

    /**
     * @param $nodeRef
     * @return AlfrescoBuilder
     */
    public static function getMetadataWithNodeRef($nodeRef)
    {
        $result = static::where('nodeRef', $nodeRef)
            ->setParser([static::class, 'parseResultMetadata'])
            ->url('file_metadata');

        return $result;
    }

    /**
     *
     * @static getInformationWithNodeRef
     *
     * @param $nodeRef
     *
     * @return AlfrescoBuilder
     */
    public static function getInformationWithNodeRef($nodeRef)
    {
        $nodeRef = explode('/', str_replace('://', '/', $nodeRef));
        $result = static::whereInUrl('store_type', $nodeRef[0])
            ->whereInUrl('store_id', $nodeRef[1])
            ->whereInUrl('id', $nodeRef[2])
            ->setParser([static::class, 'parseResultInformations'])
            ->url('file_information');

        return $result;
    }

    public function getMetadata($key)
    {
        if (array_key_exists($key, $this->metadatas)) {
            return $this->metadatas[$key];
        }
        return null;
    }

    public function getMetadatas()
    {
        return $this->metadatas;
    }

    public function addMetadata($key, $value)
    {
        $key = Str::removeBetween('{', '}', $key);

        $this->metadatas[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     * @return AlfrescoFile
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getPermission($key)
    {
        if (array_key_exists($key, $this->permissions)) {
            return $this->permissions[$key];
        }
        return null;
    }

    public function addPermission($key, $value)
    {
        $key = Str::removeBetween('{', '}', $key);

        $this->permissions[$key] = $value;
        return $this;
    }

    public function hasPermission($key = null)
    {
        if (empty($key)) {
            return (sizeof($this->permissions) === 0)?false:true;
        }
        return array_key_exists($key, $this->permissions);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AlfrescoFile
     */
    public function setType($type)
    {
        $type = Str::removeBetween('{', '}', $type);
        $this->type = $type;
        return $this;
    }

    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }
        if ($this->getMetadata($key)) {
            return $this->getMetadata($key);
        }

        return parent::getAttribute($key);
    }

    protected function getFromCache($attributes = [], $metadatas = [])
    {
        parent::getFromCache($attributes);
        if ($this->getId()) {
            $dataCache = static::getCache($this->getId());
            foreach ($dataCache->getMetadatas() as $metadata => $value) {
                $this->addMetadata($metadata, $value);
            }
            if (empty($this->getType())) {
                $this->setType($dataCache->getType());
            }
            if (empty($this->getPermissions())) {
                $this->setPermissions($dataCache->getPermissions());
            }
        }

        return $this;
    }

    /**
     * @param AlfrescoFile $result
     * @return AlfrescoFile|mixed|null
     */
    protected function parseResultMetadata($result)
    {
        $result->getFromCache($result->getAttributes(), $result->getMetadatas());
        if (!isset($result->properties) or empty($result->properties)) {
            return $result;
        }
        foreach ($result->properties as $property => $value) {
            $result->addMetadata($property, $value);
        }

        if (!empty($result->getAttribute('type'))) {
            $result->setType($result->getAttribute('type'));
        }

        $result->saveInCache();
        return $result;
    }

    /**
     * @param AlfrescoFile $result
     * @return AlfrescoFile
     */
    protected function parseResultInformations($result)
    {
        $result->getFromCache($result->getAttributes(), $result->getMetadatas());
        if (!empty($result->getAttribute('permissions'))) {
            $result->setPermissions($result->getAttribute('permissions'));
        }
        $result->saveInCache();
        return $result;
    }

}