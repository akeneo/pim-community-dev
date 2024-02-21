<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class ProductQueryBuilder extends AbstractEntityWithValuesQueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        return parent::execute();
    }
}
