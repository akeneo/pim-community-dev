<?php

namespace Oro\Bundle\OrganizationBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Get Business Units tree
     *
     * @param User $entity
     * @return array
     */
    public function getBusinessUnitsTree(User $entity = null)
    {
        return $this->getBusinessUnitRepo()->getBusinessUnitsTree($entity);
    }

    /**
     * @param User $entity
     * @param array $businessUnits
     */
    public function assignBusinessUnits($entity, array $businessUnits)
    {
        if ($businessUnits) {
            $businessUnits = $this->getBusinessUnitRepo()->getBusinessUnits($businessUnits);
        } else {
            $businessUnits = new ArrayCollection();
        }
        $entity->setBusinessUnits($businessUnits);
    }

    /**
     * @return BusinessUnitRepository
     */
    public function getBusinessUnitRepo()
    {
        return $this->em->getRepository('OroOrganizationBundle:BusinessUnit');
    }
}
