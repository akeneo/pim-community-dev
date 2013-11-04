<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;

class SearchResultProperty extends TwigTemplateProperty
{
    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $this->getTemplate()->render(
            array(
                'indexer_item' => $record->getValue('indexer_item'),
                'entity'       => $record->getValue('entity'),
            )
        );
    }
}
