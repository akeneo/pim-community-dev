<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Association entity
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductAssociation extends AbstractAssociation implements PublishedProductAssociationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProductModels(): Collection
    {
        $originalProductAssociation = $this->owner->getOriginalProduct()->getAssociationForType($this->associationType);
        if (null !== $originalProductAssociation) {
            return $originalProductAssociation->getProductModels();
        }

        return new ArrayCollection();
    }
}
