<?php

namespace Oro\Bundle\ImportExportBundle\Writer;

use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;

class DummyWriter implements ItemWriterInterface
{
    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
    }
}
