<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
    /** @var array */
    protected $groups;

    /** @var ProductManager */
    protected $productManager;

    public function __construct(ProductManager $productManager)
    {
        $this->groups = new ArrayCollection();
        $this->productManager = $productManager;
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
            ->productManager
            ->getStorageManager()
            ->getRepository('PimCatalogBundle:Group')
            ->findAll();

        return array(
            'groups' => $groups,
        );
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

        $this->productManager->saveAll($products);
    }

    protected function addProductsToGroup(array $products, Group $group)
    {
        foreach ($products as $product) {
            $group->addProduct($product);
        }
    }
}
