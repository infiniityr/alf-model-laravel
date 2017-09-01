<?php
/**
 * Nom du fichier : AlfrescoProcessor.php
 * Projet : AlfrescoAPI
 * Date : 28/08/2017
 */

namespace Infiniityr\Alfresco\API;

use Infiniityr\Alfresco\Alfresco;
use Infiniityr\Alfresco\Builders\AlfrescoBuilder;

class AlfrescoProcessor
{
    /**
     * Process the results of a "select" query.
     *
     * @param  AlfrescoBuilder  $query
     * @param  array  $results
     * @return Alfresco
     */
    public function processSelect(AlfrescoBuilder $query, $results)
    {
        $query->checkError($results);
        if (!is_array($results)) {
            $results = $this->resultToArray($query, $results);
        }
        $model = $query->getModel()->newFromBuilder($results);
        return $model;
    }

    /**
     * Process an  "insert get ID" query.
     *
     * @param  AlfrescoBuilder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(AlfrescoBuilder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return $results;
    }

    protected function resultToArray(AlfrescoBuilder $query, $results)
    {
        $format = explode(';', $query->getConnection()->content_type)[0];
        switch ($format)
        {
            case 'application/json':
                $result = json_decode($results, true);
                break;
            case 'text/xml':
                $result = json_decode(json_encode(simplexml_load_string($results, "SimpleXMLElement", LIBXML_NOBLANKS)), true);
                break;
            default:
                $result = json_decode($results, true);
        }
        return $result;
    }

}