<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Attribute group manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupManager implements SaverInterface, BulkSaverInterface, RemoverInterface
{
    /** @var AttributeGroupRepository */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager            $objectManager Object manager
     * @param AttributeGroupRepository $repository    Repository
     */
    public function __construct(ObjectManager $objectManager, AttributeGroupRepository $repository)
    {
        $this->repository = $repository;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof AttributeGroup) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Entity\AttributeGroup, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->persist($object);
        if ($options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        $options = array_merge(['flush' => true], $options);
        foreach ($objects as $object) {
            $this->save($object, ['flush' => false]);
        }

        if ($options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof AttributeGroup) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Entity\AttributeGroup, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->objectManager->remove($object);
        $this->objectManager->flush();
    }

    /**
     * Remove an attribute from a group and link it to the default group
     *
     * @param AttributeGroup     $group
     * @param AttributeInterface $attribute
     *
     * @throws \LogicException
     */
    public function removeAttribute(AttributeGroup $group, AttributeInterface $attribute)
    {
        if (null === $default = $this->repository->findDefaultAttributeGroup()) {
            throw new \LogicException('The default attribute group should exist.');
        }

        $group->removeAttribute($attribute);
        $attribute->setGroup($default);

        $this->objectManager->persist($group);
        $this->objectManager->persist($attribute);
        $this->objectManager->flush();
    }

    /**
     * Add attributes to a group
     *
     * @param AttributeGroup       $group
     * @param AttributeInterface[] $attributes
     */
    public function addAttributes(AttributeGroup $group, $attributes)
    {
        $maxOrder = $group->getMaxAttributeSortOrder();
        foreach ($attributes as $attribute) {
            $maxOrder++;
            $attribute->setSortOrder($maxOrder);
            $group->addAttribute($attribute);
            $this->objectManager->persist($attribute);
        }

        $this->objectManager->persist($group);
        $this->objectManager->flush();
    }
}
