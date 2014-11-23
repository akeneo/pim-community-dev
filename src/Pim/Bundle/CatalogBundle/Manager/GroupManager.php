<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Event\GroupEvents;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Group manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager implements SaverInterface, RemoverInterface
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $groupClass;

    /** @var string */
    protected $groupTypeClass;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param RegistryInterface        $doctrine
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $groupClass
     * @param string                   $groupTypeClass
     * @param string                   $productClass
     * @param string                   $attributeClass
     */
    public function __construct(
        RegistryInterface $doctrine,
        EventDispatcherInterface $eventDispatcher,
        $groupClass,
        $groupTypeClass,
        $productClass,
        $attributeClass
    ) {
        $this->doctrine        = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
        $this->groupClass      = $groupClass;
        $this->groupTypeClass  = $groupTypeClass;
        $this->productClass    = $productClass;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * Get available axis
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractAttribute[]
     */
    public function getAvailableAxis()
    {
        return $this->getAttributeRepository()->findAllAxis();
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
        $types = $this->getGroupTypeRepository()->findBy(array('variant' => $isVariant));

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
        return $this->doctrine->getRepository($this->groupClass);
    }

    /**
     * Returns the group type repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->doctrine->getRepository($this->groupTypeClass);
    }

    /**
     * {@inheritdoc}
     */
    public function save($group, array $options = [])
    {
        if (!$group instanceof Group) {
            throw new \InvalidArgumentException(
                sprintf('Expects a "Pim\Bundle\CatalogBundle\Entity\Group", "%s" provided.', get_class($group))
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->doctrine->getManager()->persist($group);
        if (true === $options['flush']) {
            $this->doctrine->getManager()->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($group, array $options = [])
    {
        if (!$group instanceof Group) {
            throw new \InvalidArgumentException(
                sprintf('Expects a "Pim\Bundle\CatalogBundle\Entity\Group", "%s" provided.', get_class($group))
            );
        }

        $this->eventDispatcher->dispatch(GroupEvents::PRE_REMOVE, new GenericEvent($group));

        $options = array_merge(['flush' => true], $options);
        $em = $this->doctrine->getManager();
        $em->remove($group);
        if (true === $options['flush']) {
            $em->flush();
        }
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

        $count = count($products);
        if ($count > $maxResults) {
            array_pop($products);
            $count = $manager->createQueryBuilder()
                ->select('COUNT(p)')
                ->from($this->productClass, 'p')
                ->innerJoin('p.groups', 'g', 'WITH', 'g=:group')
                ->setParameter('group', $group)
                ->getQuery()
                ->getSingleScalarResult();
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
