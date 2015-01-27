<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\Persistence\BulkSaverInterface;
use Akeneo\Component\Persistence\RemoverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;

/**
 * Attribute group manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupManager implements SaverInterface, BulkSaverInterface, RemoverInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $repository;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager                     $objectManager Object manager
     * @param AttributeGroupRepositoryInterface $repository    Repository
     */
    public function __construct(ObjectManager $objectManager, AttributeGroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof AttributeGroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface", "%s" provided',
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
        if (!$object instanceof AttributeGroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface", "%s" provided',
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
     * @param AttributeGroupInterface $group
     * @param AttributeInterface      $attribute
     *
     * @throws \LogicException
     */
    public function removeAttribute(AttributeGroupInterface $group, AttributeInterface $attribute)
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
     * @param AttributeGroupInterface $group
     * @param AttributeInterface[]    $attributes
     */
    public function addAttributes(AttributeGroupInterface $group, $attributes)
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
