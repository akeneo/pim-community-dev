<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Structure\Component\Model\AssociationType;

trait QuantifiedAssociationsTestCaseTrait
{
    protected function createQuantifiedAssociationType(string $code): AssociationType
    {
        $associationType = new AssociationType();
        $associationType->setCode($code);
        $associationType->setIsQuantified(true);

        $this->get('pim_catalog.saver.association_type')->save($associationType);

        return $associationType;
    }
}
