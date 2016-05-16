<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:00
 */

namespace MIA3\Saku;


class Index
{
    /*
     * @var IndexAdapterInterface
     */
    protected $adapter;

    public function __construct($adapterConfiguration) {
        $this->adapter = new $adapterConfiguration['adapter']($adapterConfiguration);
    }

    public function addObject($object, $objectId) {
        $this->adapter->addObject($object, $objectId);
    }

    public function search($query, $options = array()) {
        $searchResults = new SearchResults($this->adapter, $query, $options);
        return $searchResults;
    }
}