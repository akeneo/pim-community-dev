<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;

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
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $productClass
     * @param string            $attributeClass
     */
    public function __construct(RegistryInterface $doctrine, $productClass, $attributeClass)
    {
        $this->doctrine = $doctrine;
        $this->productClass  = $productClass;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Get available axis
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute[]
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
     * Get choices
     *
     * @return array
     */
    public function getChoices()
    {
        $choices = $this->getRepository()->getChoices();
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
        $types = $this->doctrine
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
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->doctrine->getRepository('PimCatalogBundle:Group');
    }

    /**
     * Returns the group type repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->doctrine->getRepository('PimCatalogBundle:GroupType');
    }

    /**
     * Removes a group
     *
     * @param Group $group
     */
    public function remove(Group $group)
    {
        $em = $this->doctrine->getManager();
        $em->remove($group);
        $em->flush();
    }

    /**
     * Returns an array containing a limited number of product groups, and the total number of products
     *
     * @param Group   $group
     * @param integer $maxResults
     *
     * @return array
     */
    public function getProductList(Group $group, $maxResults)
    {
        $manager = $this->doctrine->getManager();
        $products = $manager
            ->createQueryBuilder()
            ->select('p')
            ->from($this->productClass, 'p')
            ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
            ->setParameter('group', $group)
            ->getQuery()
            ->setMaxResults($maxResults + 1)
            ->execute();

        if (count($products) > $maxResults) {
            array_pop($products);
            $count = $manager->createQueryBuilder()
                ->select('COUNT(p)')
                ->from($this->productClass, 'p')
                ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
                ->setParameter('group', $group)
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $count = count($products);
        }

        return array(
            'products'      => $products,
            'productCount'  => $count
        );
    }

    /**
     * Get the attribute repository
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository
     */
    protected function getAttributeRepository()
    {
        return $this->doctrine->getRepository($this->attributeClass);
    }
}
