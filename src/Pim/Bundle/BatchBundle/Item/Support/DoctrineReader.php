<?php

namespace Pim\Bundle\BatchBundle\Item\Support;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Doctrine\ORM\AbstractQuery;

class DoctrineReader implements ItemReaderInterface
{
    protected $query;
    private $cursor;

    public function setQuery(AbstractQuery $query)
    {
        $this->query = $query;
    }

    public function read()
    {
        $item = null;
        if (!$this->cursor) {
            $this->cursor = $this->query->execute();
            $item = current($this->cursor);
        }

        if ($item) {
            return $item;
        }

        return next($this->cursor) ?: null;
    }
}
