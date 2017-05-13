<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:04
 */

namespace MIA3\Saku\Adapter;


interface IndexAdapterInterface
{
    public function addObject($object, $objectId, $indexName = null);

    public function getFacet($configuration);
}
