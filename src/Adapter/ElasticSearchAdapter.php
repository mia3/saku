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

    protected $defaultParams;

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
            'type' => $configuration['type']
        );
        $this->client = ClientBuilder::create()->setHosts($configuration['hosts'])->build();

//        $params = ['index' => $configuration['index']];
//        $response = $this->client->indices()->delete($params);

        $params['index']  = $configuration['index'];
        if ($this->client->indices()->exists($params) == FALSE) {
            $params = [
                'index' => $configuration['index'],
                'body' => [
                    $configuration['type'] => [
                        '_source' => [
                            'enabled' => true
                        ],
                        "_timestamp" => [
                            "enabled" => true
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer'
                            ],
                            'pageUrl' => [
                                'type' => 'string',
                                'analyzer' => 'standard'
                            ],
                            'L' => [
                                'type' => 'integer'
                            ],
                            'pageTitle' => [
                                'type' => 'string'
                            ],
                            'content' => [
                                'type' => 'string'
                            ],
                            "indexedAt" => [
                                "type" => "date"
                            ]
                        ]
                    ]
                ]
            ];

            $this->client->indices()->create($params);
        }

//        $this->client->indices()->putMapping($params);
    }

    public function addObject($object, $objectId) {
        $objectId = sha1($objectId);
        $params = array_replace(
            $this->defaultParams,
            array(
                'id' => $objectId,
                'body' => $object
            )
        );
        $this->client->index($params);
        return $objectId;
    }

    public function search($query, $options) {
        $limit = isset($options['resultsPerPage']) ? $options['resultsPerPage'] : 10;
        $offset = (isset($options['page']) ? (($options['page']) * $options['resultsPerPage']) : 0);
        $params = array_replace(
            $this->defaultParams,
            [
                'size' => $limit,
                'from' =>  $offset,
                'body' => [
                    'query' => [
                        'match' => [
                            'content' => $query
                        ]
                    ]
                ]
            ]
        );

        $results = $this->client->search($params);
        $hits = array();
        foreach ($results['hits']['hits'] as $hit) {
            $hits[] = $hit['_source'];
        }
        return array(
            'results' => $hits,
            'total' => $results['hits']['total']
        );
    }

}