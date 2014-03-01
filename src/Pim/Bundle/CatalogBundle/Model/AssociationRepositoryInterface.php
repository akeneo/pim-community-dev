<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Interface for association repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationRepositoryInterface
{
    /**
     * Return the number of Associations for a specific association type
     *
     * @param AssociationType $associationType
     *
     * @return mixed
     */
    public function countForAssociationType(AssociationType $associationType);
}
