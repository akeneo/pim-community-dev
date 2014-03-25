<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;

/**
 * Association type manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeManager
{
    /**
     * @var AssociationTypeRepository $repository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param AssociationTypeRepository $repository
     */
    public function __construct(AssociationTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get association types
     *
     * @return array
     */
    public function getAssociationTypes()
    {
        return $this->repository->findAll();
    }
}
