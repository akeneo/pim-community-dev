<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
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
class AttributeGroupManager
{
    /** @var AttributeGroupRepositoryInterface */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $attributeSaver;

    /** @var SaverInterface */
    protected $groupSaver;

    /**
     * @param AttributeGroupRepositoryInterface $repository
     * @param SaverInterface                    $groupSaver
     * @param BulkSaverInterface                $attributeSaver
     */
    public function __construct(
        AttributeGroupRepositoryInterface $repository,
        SaverInterface $groupSaver,
        BulkSaverInterface $attributeSaver
    ) {
        $this->repository     = $repository;
        $this->attributeSaver = $attributeSaver;
        $this->groupSaver     = $groupSaver;
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

        $this->attributeSaver->saveAll([$attribute]);
        $this->groupSaver->save($group);
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
        }
        $this->attributeSaver->saveAll($attributes);
        $this->groupSaver->save($group);
    }
}
