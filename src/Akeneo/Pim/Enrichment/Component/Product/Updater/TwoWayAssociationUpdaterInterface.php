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
     * TODO PHP8 type hint first argument with ProductInterface|ProductModelInterface union type
     *
     * @param ProductInterface|ProductModelInterface $owner
     * @param string $associationTypeCode
     * @param EntityWithAssociationsInterface $associatedEntity
     *
     * @throws \LogicException
     */
    public function createInversedAssociation(
        $owner,
        string $associationTypeCode,
        EntityWithAssociationsInterface $associatedEntity
    ): void;

    /**
     * An association has been removed, this method should remove the inversed association if there is one.
     *
     * TODO PHP8 type hint first argument with ProductInterface|ProductModelInterface union type
     *
     * @param ProductInterface|ProductModelInterface $owner
     * @param string $associationTypeCode
     * @param EntityWithAssociationsInterface $associatedEntity
     *
     * @throws \LogicException
     */
    public function removeInversedAssociation(
        $owner,
        string $associationTypeCode,
        EntityWithAssociationsInterface $associatedEntity
    ): void;
}
