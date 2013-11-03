<?php

namespace Oro\Bundle\SearchBundle\Extension;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\TwigTemplateProperty;

class SearchResultProperty extends TwigTemplateProperty
{
    /** @var EntityManager */
    protected $em;

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
