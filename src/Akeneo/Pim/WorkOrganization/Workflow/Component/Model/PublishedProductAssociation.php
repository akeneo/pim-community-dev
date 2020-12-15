<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Webmozart\Assert\Assert;

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
        Assert::implementsInterface($this->owner, PublishedProductInterface::class);
        $productModels = $this->owner->getOriginalProduct()->getAssociatedProductModels($this->associationType->getCode());

        return $productModels ? $productModels : new ArrayCollection();
    }
}
