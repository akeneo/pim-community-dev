<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Published product association interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface PublishedProductAssociationInterface extends AssociationInterface
{
    /**
     * @return Collection
     */
    public function getProductModels(): Collection;
}
