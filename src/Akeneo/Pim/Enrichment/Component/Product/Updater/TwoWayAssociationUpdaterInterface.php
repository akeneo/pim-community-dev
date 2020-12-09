<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

interface TwoWayAssociationUpdaterInterface
{
    /**
     * An association has been created, this method should create the inversed association.
     *
     * @param EntityWithAssociationsInterface $owner
     * @param string $associationTypeCode
     * @param ProductInterface|ProductModelInterface $associatedEntity
     * TODO PHP8 type hint with the two interfaces
     *
     * @throws \LogicException
     */
    public function createInversedAssociation(
        EntityWithAssociationsInterface $owner,
        string $associationTypeCode,
        $associatedEntity
    ): void;

    /**
     * An association has been removed, this method should remove the inversed association if there is one.
     *
     * @param EntityWithAssociationsInterface $owner
     * @param string $associationTypeCode
     * @param ProductInterface|ProductModelInterface $associatedEntity
     * TODO PHP8 type hint with the two interfaces
     *
     * @throws \LogicException
     */
    public function removeInversedAssociation(
        EntityWithAssociationsInterface $owner,
        string $associationTypeCode,
        $associatedEntity
    ): void;
}
