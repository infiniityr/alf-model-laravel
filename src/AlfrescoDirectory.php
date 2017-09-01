<?php
/**
 * Nom du fichier : AlfrescoDirectory.php
 * Projet : AlfrescoAPI
 * Date : 29/08/2017
 */

namespace Infiniityr\Alfresco;


use Infiniityr\Alfresco\Builders\AlfrescoBuilder;
use Infiniityr\Alfresco\Builders\AlfrescoBuilderDirectory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AlfrescoDirectory extends AlfrescoElement
{
    /**
     *  $children
     * @var Collection
     */
    protected $children = [];

    public function getInformations($nodeRef = null)
    {
        $nodeRef = ($nodeRef)?:$this->getId();
        if (!$nodeRef) {
            return false;
        }
        if ($this->hasCache($nodeRef)) {
            $this->getFromCache($this->getAttributes(), $this->getChildren());
            if (!empty($this->getAttributes())) {
                return $this;
            }
        }
        $nodeRefExplode = explode('/', str_replace('://', '/', $nodeRef));
        $query = static::whereDirectoryNodeRef($nodeRefExplode[2], $nodeRefExplode[0], $nodeRefExplode[1]);

        $model = $query->get();
        if ($model instanceof AlfrescoDirectory and $model->hasChildren()) {
            foreach ($model->getAttributes() as $attribute => $value) {
                $this->setAttribute($attribute, $value);
            }
            foreach ($model->getChildrenId() as $id) {
                $this->addChild($id);
            }
            $this->setChildrenId($model->getChildrenId());
        }

        $this->saveInCache();
        return $this;
    }

    /**
     * @param $id
     * @param string $store_type
     * @param string $store_id
     * @param string $type
     * @param string $path
     *
     * @return AlfrescoBuilder
     */
    public static function whereDirectoryNodeRef($id, $store_type = 'workspace', $store_id = 'SpacesStore', $type = 'all', $path = '')
    {
        $result = static::whereInUrl('store_id', $store_id)
            ->whereInUrl('store_type', $store_type)
            ->whereInUrl('id', $id)
            ->whereInUrl('type', $type)
            ->whereInUrl('path', $path)
            ->parser([AlfrescoDirectory::class, 'parseResultInformations'])
            ->url('content_repository_node');

        return $result;
    }

    /**
     * @param $site
     * @param $path
     * @param string $type
     * @param string $container
     *
     * @return AlfrescoBuilder
     */
    public static function whereDirectoryPath($site, $path, $type = 'all', $container = 'documentLibrary')
    {
        $result = static::whereInUrl('site2', $site)
            ->whereInUrl('path', $path)
            ->whereInUrl('type', $type)
            ->whereInUrl('container', $container)
            ->parser([AlfrescoDirectory::class, 'parseResultInformations'])
            ->url('content_repository_path');

        return $result;
    }

    /**
     * @return Collection
     */
    public function getChildren()
    {
        $children = [];

        $this->getChildrenId()->each(function($item, $key) use (&$children){
            if ($this->hasCache($item)) {
                $children[$key] = static::getCache($item);
            }
            else {
                $model = AlfrescoFile::get($item);
                if ($model->getType() == "folder") {
                    $model = AlfrescoDirectory::get($item);
                }
                $children[$key] = $model;
            }
        });

        return collect($children);
    }

    /**
     * @return Collection
     */
    public function getChildrenId()
    {
        if (is_array($this->children)) {
            $this->children = collect($this->children);
        }
        return $this->children;
    }

    public function children()
    {
        return $this->getChildren();
    }

    /**
     * @param Collection|array|Alfresco $children
     * @return AlfrescoDirectory
     * @throws \Exception
     */
    public function setChildren($children)
    {
        if (is_null($children)) {
            throw new \Exception("Can't set null children");
        }
        if (is_array($children)) {
            $children = collect($children);
        }
        $children->each(function($item, $key){
            $this->addChild($item);
        });
        return $this;
    }

    /**
     * @param AlfrescoFile|AlfrescoDirectory|string $child
     * @return AlfrescoDirectory
     */
    public function addChild($child = null)
    {
        if ($child instanceof AlfrescoElement) {
            $child = $child->getId();
        }
        if (!Arr::exists($this->getChildrenId(), $child)) {
            $this->getChildrenId()->put($this->getChildrenId()->count(), $child);
        }
        return $this;
    }

    public function setChildrenId($childrenId)
    {
        if (is_array($childrenId)) {
            $childrenId = collect($childrenId);
        }
        $this->children = $childrenId;
    }

    public function hasChildren()
    {
        if (!$this->children) {
            return false;
        }
        return ($this->children->count() === 0)?false:true;
    }

    public function getFromCache($attributes = [], $children = [])
    {
        parent::getFromCache($attributes);
        if ($this->getId()) {
            $dataCache = static::getCache($this->getId());
            if ($dataCache instanceof static) {
                if ($dataCache->hasChildren()) {
                    $this->setChildren($dataCache->getChildrenId());
                }
            }
        }
        return $this;
    }

    /**
     * @param AlfrescoDirectory $result
     * @return static
     */
    protected function parseResultInformations($result)
    {
        $result->getFromCache($result->getAttributes(), $result->getChildren());
        if (isset($result->items) and sizeof($result->items) > 0) {
            $files = [];
            foreach ($result->items as $key => $file) {
                $nodeRef = $file['node']['nodeRef'];
                if ($file['node']['isContainer']) {
                    $files[$key] = AlfrescoDirectory::get($nodeRef, $result->getId());
                }
                else{
                    $files[$key] = AlfrescoFile::get($nodeRef, $result->getId());
                }
            }
            $result->setChildren(collect($files));
        }
        $result->saveInCache();
        return $result;
    }
}