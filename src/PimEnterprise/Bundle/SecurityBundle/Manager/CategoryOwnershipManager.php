<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\CategoryOwnership;

/**
 * Category ownership manager
  *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryOwnershipManager
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Grant ownership to a role for the specified categories
     *
     * @param Role              $role
     * @param CategoryInterface $category
     */
    public function grantOwnership(Role $role, CategoryInterface $category)
    {
        $ownership = $this->findOwnership($role, $category);

        if (!$ownership) {
            $ownership = new CategoryOwnership();
            $ownership->setRole($role)->setCategory($category);
            $this->manager->persist($ownership);
        }
    }

    /**
     * Revoke ownership to a role for the specified categories
     *
     * @param Role              $role
     * @param CategoryInterface $category
     */
    public function revokeOwnership(Role $role, CategoryInterface $category)
    {
        $ownership = $this->findOwnership($role, $category);

        if ($ownership) {
            $this->manager->remove($ownership);
        }
    }

    /**
     * Get categories owned by a role
     *
     * @param Role $role
     *
     * @return ArrayCollection
     */
    public function getOwnedCategories(Role $role)
    {
        $ownerships = $this->getRepository()->findBy(['role' => $role]);

        $categories = new ArrayCollection();

        foreach ($ownerships as $ownership) {
            $categories[] = $ownership->getCategory();
        }

        return $categories;
    }

    /**
     * Find ownership for a role and category
     *
     * @param Role              $role
     * @param CategoryInterface $category
     *
     * @return CategoryOwnership|null
     */
    protected function findOwnership(Role $role, CategoryInterface $category)
    {
        return $this->getRepository()->findOneBy(['role' => $role, 'category' => $category]);
    }

    /**
     * Get repository
     *
     * @return CategoryOwnershipRepository
     */
    protected function getRepository()
    {
        return $this->manager->getRepository('PimEnterpriseSecurityBundle:CategoryOwnership');
    }
}
