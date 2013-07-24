<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

class ORMCursorReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    protected $query;
    private $cursor;

    public function setQuery(AbstractQuery $query)
    {
        $this->query = $query;
    }

    public function read()
    {
        if (!$this->cursor) {
            $this->cursor = $this->query->iterate();
        }

        return $this->cursor->next() ?: null;
    }

    public function getConfigurationFields()
    {
        return array();
    }
}
