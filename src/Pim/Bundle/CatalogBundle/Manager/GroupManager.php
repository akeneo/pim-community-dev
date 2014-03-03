<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

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
     * @var AttributeRepository $attributeRepository
     */
    protected $attributeRepository;

    /**
     * @var GroupRepository $groupRepository
     */
    protected $groupRepository;

    /**
     * @var GroupTypeRepository $groupTypeRepository
     */
    protected $groupTypeRepository;

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * Constructor
     *
     * @param EntityManager       $em
     * @param GroupRepository     $groupRepository
     * @param GroupTypeRepository $groupTypeRepository
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        EntityManager $em,
        GroupRepository $groupRepository,
        GroupTypeRepository $groupTypeRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->em = $em;
        $this->groupRepository     = $groupRepository;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Get available axis
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute[]
     */
    public function getAvailableAxis()
    {
        return $this->attributeRepository->findAllAxis();
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
        $types = $this->groupTypeRepository->findBy(array('variant' => $isVariant));

        $choices = array();
        foreach ($types as $type) {
            $choices[$type->getId()] = $type->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Returns the group repository
     *
     * @return GroupRepository
     */
    public function getRepository()
    {
        return $this->groupRepository;
    }

    /**
     * Returns the group type repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->groupTypeRepository;
    }

    /**
     * Removes a group
     *
     * @param Group $group
     */
    public function remove(Group $group)
    {
        $this->em->remove($group);
        $this->em->flush();
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
        $products = $this->em
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
            $count = $this->em->createQueryBuilder()
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
}
