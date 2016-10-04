<?php
/**
 * Created by PhpStorm.
 * User: mneuhaus
 * Date: 14.05.16
 * Time: 23:10
 */

namespace MIA3\Saku;


use MIA3\Saku\Adapter\IndexAdapterInterface;

class ArrayIterator implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $internalIterator;

    protected function load() {}

    protected function setArray($array)
    {
        $this->internalIterator = new \ArrayIterator($array);
    }

    public function append($value)
    {
        $this->load();
        return $this->internalIterator->append($value);
    }

    public function count()
    {
        $this->load();
        return $this->internalIterator->count();
    }

    public function current()
    {
        $this->load();
        return $this->internalIterator->current();
    }

    public function key()
    {
        $this->load();
        return $this->internalIterator->key();
    }

    public function next()
    {
        $this->load();
        return $this->internalIterator->next();
    }

    public function rewind()
    {
        $this->load();
        return $this->internalIterator->rewind();
    }

    public function seek($position)
    {
        $this->load();
        return $this->internalIterator->seek($position);
    }

    public function valid()
    {
        $this->load();
        return $this->internalIterator->valid();
    }

}