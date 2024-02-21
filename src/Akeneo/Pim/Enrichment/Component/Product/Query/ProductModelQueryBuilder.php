<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class ProductModelQueryBuilder extends AbstractEntityWithValuesQueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);

        return parent::execute();
    }
}
