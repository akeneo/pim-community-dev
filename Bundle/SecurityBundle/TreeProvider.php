<?php

namespace Oro\Bundle\SecurityBundle;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
class TreeProvider
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function fillTree(OwnerTree $tree)
    {
        $users = $this->em->getRepository('Oro\Bundle\UserBundle\Entity\User')->findAll();
        $businessUnits = $this->em->getRepository('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit')->findAll();



        foreach ($users as $user) {
            /** @var \Oro\Bundle\UserBundle\Entity\User $user */
            $tree->addUser($user->getId(), $user->getOwner()->getId());

        }

        foreach ($businessUnits as $businessUnit)
        {
            /** @var \Oro\Bundle\OrganizationBundle\Entity\BusinessUnit $businessUnit */
            $tree->addBusinessUnit($businessUnit->getId(), $businessUnit->getOrganization()->getId());
            if ($businessUnit->getOwner()) {
                $tree->addBusinessUnitRelation($businessUnit->getId(), $businessUnit->getOwner()->getId());
            }
        }

        foreach ($users as $user) {
            /** @var \Oro\Bundle\UserBundle\Entity\User $user */
            foreach ($user->getBusinessUnits() as $businessUnit) {
                $tree->addUserBusinessUnit($user->getId(), $businessUnit->getId());
            }
        }
    }
} 