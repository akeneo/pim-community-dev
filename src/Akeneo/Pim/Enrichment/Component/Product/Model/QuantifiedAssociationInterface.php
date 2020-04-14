<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Association interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface QuantifiedAssociationInterface extends ReferableInterface
{
    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get association type
     *
     * @return AssociationTypeInterface
     */
    public function getAssociationType();

    /**
     * Set association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @return AssociationInterface
     */
    public function setAssociationType(AssociationTypeInterface $associationType);

    /**
     * Get owner
     *
     * @return EntityWithAssociationsInterface
     */
    public function getOwner();

    /**
     * Set owner
     *
     * @param EntityWithAssociationsInterface $owner
     *
     * @return AssociationInterface
     */
    public function setOwner(EntityWithAssociationsInterface $owner);
}
