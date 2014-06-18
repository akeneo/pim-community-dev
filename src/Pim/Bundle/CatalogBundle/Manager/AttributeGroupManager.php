<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Attribute group manager
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupManager
{
    /**
     * @var AttributeGroupRepository
     */
    protected $repository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManager             $objectManager   Object manager
     * @param AttributeGroupRepository  $repository      Repository
     */
    public function __construct(ObjectManager $objectManager, AttributeGroupRepository $repository) {
        $this->repository = $repository;
        $this->objectManager = $objectManager;
    }

    /**
     * Remove an attribute from a group and link it to the default group
     *
     * @param AttributeGroup    $group
     * @param AbstractAttribute $attribute
     *
     * @throws \LogicException
     *
     * @return AbstractAttribute
     */
    public function removeAttribute(AttributeGroup $group, AbstractAttribute $attribute)
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
}
