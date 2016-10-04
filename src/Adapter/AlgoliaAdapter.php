<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:07
 */

namespace MIA3\Saku\Adapter;

class AlgoliaAdapter implements IndexAdapterInterface
{
    /**
     * @var \AlgoliaSearch\Client
     */
    protected $client;

    protected $optionMapping = array(
        'page' => 'page',
        'resultsPerPage' => 'hitsPerPage'
    );

    public function __construct($configuration)
    {
        $configuration = array_replace(array(
            'index' => 'saku'
        ), $configuration);

        $this->client = new \AlgoliaSearch\Client(
            $configuration['applicationId'],
            $configuration['apiKey']
        );

        $this->index = $this->client->initIndex($configuration['index']);
    }

    public function addObject($object, $objectId) {
        try {
            $existingObject = $this->index->getObject($objectId);
            $object['objectID'] = $objectId;
            $this->index->saveObject($object);
        } catch(\AlgoliaSearch\AlgoliaException $exception) {
            if ($exception->getMessage() !== 'ObjectID does not exist') {
                throw $exception;
            }
            $this->index->addObject($object, $objectId);
        }
        return $objectId;
    }

    public function search($query, $options) {
        $algoliaOptions = array();
        foreach ($this->optionMapping as $source => $target) {
            if (!isset($options[$source])) {
                continue;
            }
            $algoliaOptions[$target] = $options[$source];
        }
        $results = $this->index->search($query, $algoliaOptions);
        return array(
            'results' => $results['hits'],
            'total' => $results['nbHits']
        );
    }

    public function getFacets($facet) {
        return array();
    }

}