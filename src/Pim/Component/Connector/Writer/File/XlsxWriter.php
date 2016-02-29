<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;

class XlsxWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        // TODO: Implement getConfigurationFields() method.
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        // TODO: write logic here
    }
}
