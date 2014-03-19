<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Association Manager
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationManager
{
    /** @var AssociationRepositoryInterface */
    protected $repository;

    /**
     * @param AssociationRepositoryInterface $repository
     */
    public function __construct(AssociationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get association count by association type
     *
     * @return int
     */
    public function countForAssociationType(AssociationType $type)
    {
        return $this->repository->countForAssociationType($type);

    }
}
