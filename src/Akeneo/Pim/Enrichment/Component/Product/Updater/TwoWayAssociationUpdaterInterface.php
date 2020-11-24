<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;

interface TwoWayAssociationUpdaterInterface
{
    /**
     * An association has been created, this method should create the inversed association.
     *
     * @param AssociationInterface $association
     * @param EntityWithAssociationsInterface $owner
     */
    public function createInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $owner
    ): void;

    /**
     * An association has been removed, this method should remove the inversed association if there is one.
     *
     * @param AssociationInterface $association
     * @param EntityWithAssociationsInterface $owner
     */
    public function removeInversedAssociation(
        AssociationInterface $association,
        EntityWithAssociationsInterface $owner
    ): void;
}
