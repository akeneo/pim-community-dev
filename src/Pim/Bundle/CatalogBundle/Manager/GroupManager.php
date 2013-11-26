<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Group manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get available axis
     *
     * @return ProductAttribute[]
     */
    public function getAvailableAxis()
    {
        $repo = $this->getAttributeRepository();

        return $repo->findAllAxis();
    }

    /**
     * Get axis as choice list
     *
     * @return array
     */
    public function getAvailableAxisChoices()
    {
        $attributes = $this->getAvailableAxis();

        $choices = array();
        foreach ($attributes as $attribute) {
            $choices[$attribute->getId()] = $attribute->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get axis as choice list
     *
     * @param boolean $isVariant
     *
     * @return array
     */
    public function getTypeChoices($isVariant)
    {
        $types = $this->objectManager
            ->getRepository('PimCatalogBundle:GroupType')
            ->findBy(array('variant' => $isVariant));

        $choices = array();
        foreach ($types as $type) {
            $choices[$type->getId()] = $type->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get the attribute repository
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\ProductAttributeRepository
     */
    protected function getAttributeRepository()
    {
        return $this->objectManager->getRepository('PimCatalogBundle:ProductAttribute');
    }
}
