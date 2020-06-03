<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Structure\Component\Model\AssociationType;

trait QuantifiedAssociationsTestCaseTrait
{
    protected function createQuantifiedAssociationType(string $code): AssociationType
    {
        $factory = $this->get('pim_catalog.factory.association_type');
        $updater = $this->get('pim_catalog.updater.association_type');
        $saver = $this->get('pim_catalog.saver.association_type');

        $associationType = $factory->create();
        $updater->update($associationType,  ['code' => $code, 'is_quantified' => true]);
        $saver->save($associationType);

        return $associationType;
    }
}
