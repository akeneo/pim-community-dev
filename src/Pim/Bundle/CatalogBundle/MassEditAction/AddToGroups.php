<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends AbstractMassEditAction
{
    /** @var ArrayCollection */
    protected $groups;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->groups        = new ArrayCollection();
        $this->entityManager = $entityManager;
    }

    /**
     * Set groups
     *
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = new ArrayCollection($groups);
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        $groups = $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findAll();

        return [
            'groups' => $groups,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_catalog_mass_add_to_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($this->groups as $group) {
            $this->addProductsToGroup($products, $group);
        }
    }

    /**
     * Add products to a group
     *
     * @param ProductInterface[] $products
     * @param Group              $group
     */
    protected function addProductsToGroup(array $products, Group $group)
    {
        foreach ($products as $product) {
            $group->addProduct($product);
        }
    }
}
