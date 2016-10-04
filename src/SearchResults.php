<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:10
 */

namespace MIA3\Saku;


use MIA3\Saku\Adapter\IndexAdapterInterface;

class SearchResults extends ArrayIterator
{
    /**
     * @var IndexAdapterInterface
     */
    protected $adapter;

    /**
     * @var query
     */
    protected $query;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var integer
     */
    protected $total;

    /**
     * track if any settings have changed since the last load
     *
     * @var boolean
     */
    protected $changed = false;

    public function __construct($adapter, $query, $options)
    {
        $this->changed = true;
        $this->adapter = $adapter;
        $this->query = $query;
        $this->options = $options;
    }

    protected function load()
    {
        if ($this->changed === false) {
            return;
        }
        $result = $this->adapter->search($this->query, $this->options);
        $this->total = $result['total'];
        $this->setArray($result['results']);
        $this->changed = false;
    }

    public function getTotal()
    {
        $this->load();

        return $this->total;
    }

    public function setLimit($limit)
    {
        $this->changed = true;
        $this->options['resultsPerPage'] = $limit;
    }

    public function setPage($page)
    {
        $this->changed = true;
        $this->options['page'] = $page;
    }

    public function getItemsFrom() {
        return ($this->options['resultsPerPage'] * $this->options['page']) + 1;
    }

    public function getItemsTo() {
        $to = $this->options['resultsPerPage'] * ($this->options['page'] + 1);
        if ($to > $this->getTotal()) {
            return $this->total;
        }
        return $to;
    }
}