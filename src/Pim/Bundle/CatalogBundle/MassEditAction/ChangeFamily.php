<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Pim\Bundle\CatalogBundle\Form\Type\MassEditAction\ChangeFamilyType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Batch operation to change the family of products
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeFamily extends AbstractMassEditAction
{
    /** @var ProductManager $manager */
    protected $manager;

    /** @var Family $family The family to change the product family to */
    protected $family;

    /**
     * @param ProductManager $manager
     */
    public function __construct(ProductManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Family $family
     *
     * @return ChangeFamily
     */
    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new ChangeFamilyType();
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($products as $product) {
            $product->setFamily($this->family);
        }
        $this->manager->saveAll($products, false);
    }
}
