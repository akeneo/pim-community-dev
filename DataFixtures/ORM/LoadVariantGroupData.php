<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load data for variant groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadVariantGroupData extends AbstractDemoFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled() === false) {
            return;
        }

        $this->manager = $manager;

        // get attributes
        $attributes = $this->getVariantGroupManager()->getAvailableAxis();

        // create variants
        $variant1 = $this->createVariant('AKENEO_MUG', array(current($attributes)));
        $variant2 = $this->createVariant('AKENEO_TSHIRT', array(end($attributes)));
        $variant3 = $this->createVariant('ORO_TSHIRT', $attributes);
    }

    /**
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductAttribute[]
     */
    protected function getAvailableAxis()
    {
        return $this->getVariantGroupManager()->getAvailableAxis();
    }

    /**
     * Create a variant group entity
     *
     * @param string $code
     * @param ProductAttribute[] $axes
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\VariantGroup
     */
    protected function createVariant($code, array $axes)
    {
        $variant = new VariantGroup();
        $variant->setCode($code);
        foreach ($axes as $axis) {
            $variant->addAttribute($axis);
        }

        return $variant;
    }

    /**
     * Get the variant group manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\VariantGroupManager
     */
    protected function getVariantGroupManager()
    {
        return $this->container->get('pim_catalog.manager.variant_group');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }
}
