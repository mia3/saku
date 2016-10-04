<?php
namespace MIA3\Saku\Adapter;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class ElasticSearchAdapter implements IndexAdapterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * array of default parameters used in all queries towards
     * elasticsearch
     *
     * @var array
     */
    protected $defaultParams;

    /**
     * ElasticSearchAdapter constructor.
     * @param $configuration
     */
    public function __construct($configuration)
    {
        $configuration = array_replace(
            array(
                'index' => 'saku',
                'type' => 'saku_document',
            ),
            $configuration
        );
        $this->defaultParams = array(
            'index' => $configuration['index'],
            'type' => $configuration['type'],
        );
        if (is_string($configuration['hosts'])) {
            $configuration['hosts'] = explode(',', $configuration['hosts']);
        }
        $this->client = ClientBuilder::create()->setHosts($configuration['hosts'])->build();
        $this->initializeIndex($configuration, $params);
    }

    /**
     * add an object to the elasticsearch index
     *
     * @param $object
     * @param $objectId
     * @return string
     */
    public function addObject($object, $objectId)
    {
        $objectId = sha1($objectId);
        $params = array_replace(
            $this->defaultParams,
            array(
                'id' => $objectId,
                'body' => $object,
            )
        );
        $this->client->index($params);

        return $objectId;
    }

    /**
     * search the index
     *
     * @param $query
     * @param $options
     * @return array
     */
    public function search($query, $options)
    {
        $limit = isset($options['resultsPerPage']) ? $options['resultsPerPage'] : 10;
        $offset = (isset($options['page']) ? (($options['page']) * $options['resultsPerPage']) : 0);

        $filter = [];
        if (isset($options['facets']) && !empty($options['facets'])) {
            $filter["term"] = $options['facets'];
        }
        $params = array_replace(
            $this->defaultParams,
            [
                'size' => $limit,
                'from' => $offset,
                'body' => [
                    'query' => [
                        "filtered" => [
                            "query" => [
                                "match" => [
                                    "content" => $query
                                ],
                            ],
                            "filter" => $filter
                        ]
                    ],
                ],
            ]
        );

        $results = $this->client->search($params);
        $hits = array();
        foreach ($results['hits']['hits'] as $hit) {
            $hits[] = $hit['_source'];
        }

        return array(
            'results' => $hits,
            'total' => $results['hits']['total'],
        );
    }

    /**
     * load all options of a facet
     *
     * @param array $facet
     * @return array
     */
    public function getFacet($facet)
    {
        $params = array_replace(
            $this->defaultParams,
            [
                'size' => 0,
                'body' => [
                    "aggs" => [
                        "facet" => [
                            "terms" => [
                                "field" => $facet['field'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $results = $this->client->search($params);
        $options = array();
        foreach ($results['aggregations']['facet']['buckets'] as $bucket) {
            $options[] = array(
                'value' => $bucket['key'],
                'count' => $bucket['doc_count'],
            );
        }

        return $options;
    }

    /**
     * @param $configuration
     * @param $params
     */
    public function initializeIndex($configuration, $params)
    {
//        $params = ['index' => $configuration['index']];
//        $response = $this->client->indices()->delete($params);

        $params['index'] = $configuration['index'];
        if ($this->client->indices()->exists($params) == false) {
            $params = [
                'index' => $configuration['index'],
                'body' => [
                    $configuration['type'] => [
                        '_source' => [
                            'enabled' => true,
                        ],
                        "_timestamp" => [
                            "enabled" => true,
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'pageUrl' => [
                                'type' => 'string',
                                'analyzer' => 'standard',
                            ],
                            'L' => [
                                'type' => 'integer',
                            ],
                            'pageTitle' => [
                                'type' => 'string',
                            ],
                            'content' => [
                                'type' => 'string',
                            ],
                            "indexedAt" => [
                                "type" => "date",
                            ],
                        ],
                    ],
                ],
            ];

            $this->client->indices()->create($params);
        }
//        $this->client->indices()->putMapping($params);
    }

}