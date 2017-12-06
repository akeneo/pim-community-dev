<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AssociationInterface;

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
