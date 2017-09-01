<?php
/**
 * Nom du fichier : AlfrescoBuilderSearch.php
 * Projet : AlfrescoAPI
 * Date : 30/08/2017
 */

namespace Infiniityr\Alfresco\Builders;


use Illuminate\Support\Str;

class AlfrescoBuilderSearch extends AlfrescoBuilder
{

    protected $query = [];

    protected $filters = [];

    public function in($nodeRef)
    {
        $this->where('rootNode', $nodeRef);
        return $this;
    }

    public function whereQuery($key, $value)
    {
        $this->query[$key] = $value;
        return $this;
    }

    public function whereFilter($filter, $value)
    {
        if (Str::startsWith($value, '*')) {
            $value = '*'.$value;
        }
        if (Str::endsWith($value, '*')) {
            $value = $value.'*';
        }
        $this->filters[] = $filter.'|'.$value;
    }

    protected function getWheres()
    {
        $result = parent::getWheres();
        if (sizeof($this->query) > 0) {
            $result = array_merge($result, ['query' => json_encode($this->query)]);
        }
        if (sizeof($this->filters) > 0) {
            $result = array_merge($result, ['filters' => implode(',', $this->filters)]);
        }
        return $result;
    }



}