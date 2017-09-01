<?php
/**
 * Nom du fichier : AlfrescoSearch.php
 * Projet : AlfrescoAPI
 * Date : 29/08/2017
 */

namespace Infiniityr\Alfresco;


use Infiniityr\Alfresco\Builders\AlfrescoBuilderSearch;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AlfrescoSearch extends Alfresco
{
    /**
     *  $resultSearch
     * @var Collection
     */
    protected $resultSearch;

    /**
     *
     * @param string $textSearch
     * @return AlfrescoBuilderSearch
     */
    public static function search($textSearch = '')
    {
        if (!Str::startsWith($textSearch, '*')) {
            $textSearch = '*'.$textSearch;
        }
        if (!Str::endsWith($textSearch, '*')) {
            $textSearch = $textSearch.'*';
        }
        $result = static::where('term', $textSearch)
            ->parser([AlfrescoSearch::class, 'parseSearchResult'])
            ->url('search');

        return $result;
    }

    public function newAlfrescoBuilder($connection)
    {
        return new AlfrescoBuilderSearch($this->getConnection());
    }

    /**
     * @return Collection
     */
    public function getResultSearch()
    {
        $models = [];

        $this->getResultId()->each(function($item, $key) use (&$models){
            $model = AlfrescoFile::get($item);
            if ($model instanceof AlfrescoFile and $model->getType() == "folder") {
                $model = AlfrescoDirectory::get($item);
            }
            $models[$key] = $model;
        });

        return collect($models);
    }

    /**
     * @param Collection $resultSearch
     * @return AlfrescoSearch
     */
    public function setResultSearch($resultSearch)
    {
        if (is_array($resultSearch)) {
            $resultSearch = collect($resultSearch);
        }
        $this->resultSearch = $resultSearch;
        return $this;
    }

    public function getResultId()
    {
        return $this->resultSearch;
    }

    /**
     * @param static $result
     * @return static
     */
    protected function parseSearchResult($result)
    {
        if (isset($result->items) and sizeof($result->items) > 0) {
            $files = [];
            foreach ($result->items as $key => $file) {
                $nodeRef = $file['nodeRef'];
                $files[$key] = $nodeRef;
                switch ($file['type'])
                {
                    case 'folder':
                        AlfrescoDirectory::get($nodeRef);
                        break;
                    default:
                        AlfrescoFile::get($nodeRef);
                }
            }
            $result->setResultSearch(collect($files));
        }
        return $result;
    }
}