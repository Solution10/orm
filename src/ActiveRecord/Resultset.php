<?php

namespace Solution10\ORM\ActiveRecord;

use Solution10\Collection\Collection;

class Resultset extends Collection
{
    protected $built = [];
    protected $protoModel = null;

    public function resultModel(Model $m = null)
    {
        if ($m === null) {
            return $this->protoModel;
        }
        $this->protoModel = $m;
        return $this;
    }

    /**
     * @param   mixed $offset
     * @return  Model|Model[]
     */
    public function offsetGet($offset)
    {
        $return = parent::offsetGet($offset);

        // Slightly dodgy shim to check if what we're getting is an array of results, or just a result.
        $key1 = array_keys($return)[0];
        if (is_int($key1)) {
            $result = [];
            foreach ($return as $idx => $item) {
                $result[$idx] = $this->buildInstance($idx, $item);
            }
        } else {
            $result = $this->buildInstance($offset, $return);
        }
        return $result;
    }

    public function current()
    {
        $item = parent::current();
        return $this->buildInstance($this->iter_current_pos, $item);
    }

    protected function buildInstance($index, $item)
    {
        if (!array_key_exists($index, $this->built)) {
            $instance = clone $this->protoModel;
            $instance->set($item);
            $instance->setAsSaved();
            $this->built[$index] = $instance;
        }

        return $this->built[$index];
    }
}
