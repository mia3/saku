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

    protected $configuration;

    public function __construct($configuration) {
        $this->configuration =$configuration;
        $this->adapter = new $configuration['adapter']($configuration);
    }

    public function addObject($object, $objectId) {
        $this->adapter->addObject($object, $objectId);
    }

    public function search($query, $options = array()) {
        $searchResults = new SearchResults($this->adapter, $query, $options);
        return $searchResults;
    }

    public function getFacets() {
        $facets = array();
        foreach ($this->configuration['facets'] as $facetName => $facet) {
            $facets[$facetName] = $facet;
            $facets[$facetName]['options'] = $this->adapter->getFacet($facet);
        }
        return $facets;
    }
}
