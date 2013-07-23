<?php

namespace Oro\Bundle\OrganizationBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository;

use Oro\Bundle\UserBundle\Entity\User;

class BusinessUnitManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get user query builder
     * @param User $entity
     * @return QueryBuilder
     */
    public function getBusinessUnitsTree(User $entity)
    {
        return $this->getBusinessUnitRepo()->getBusinessUnitsTree($entity);
    }

    /**
     * @param User $entity
     * @param array $businessUnits
     */
    public function assignBusinessUnits($entity, array $businessUnits)
    {
        $businessUnits = $this->getBusinessUnitRepo()->getBusinessUnits($businessUnits);
        $entity->setBusinessUnits($businessUnits);
    }

    /**
     * @return BusinessUnitRepository
     */
    protected function getBusinessUnitRepo()
    {
        return $this->em->getRepository('OroOrganizationBundle:BusinessUnit');
    }
}

