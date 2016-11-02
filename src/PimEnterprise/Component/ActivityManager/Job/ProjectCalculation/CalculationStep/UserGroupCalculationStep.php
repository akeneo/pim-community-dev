<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class UserGroupCalculationStep implements CalculationStepInterface
{
    /** @var ObjectUpdaterInterface  */
    private $projectUpdater;

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /** @var CategoryAccessRepository */
    private $categoryAccessRepository;

    /** @var AttributeGroupAccessRepository */
    private $attributeGroupAccessRepository;

    public function __construct(
        ObjectUpdaterInterface $projectUpdater,
        ObjectDetacherInterface $objectDetacher,
        CategoryAccessRepository $categoryAccessRepository,
        AttributeGroupAccessRepository $attributeGroupAccessRepository
    ) {
        $this->projectUpdater = $projectUpdater;
        $this->objectDetacher = $objectDetacher;
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->attributeGroupAccessRepository = $attributeGroupAccessRepository;
    }

    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $categories = $product->getCategories();

        $productUserGroups = [];
        foreach ($categories as $category) {
            $productUserGroups = array_merge(
                $productUserGroups,
                $this->categoryAccessRepository->getGrantedUserGroups($category, Attributes::EDIT_ITEMS)
            );

            $this->objectDetacher->detach($category);
        }

        $attributeUserGroups = [];
        $attributesRequirement = $product->getFamily()->getAttributeRequirements();
        foreach ($attributesRequirement as $attributeRequirement) {
            $attribute = $attributeRequirement->getAttribute();
            $attributeGroup = $attribute->getGroup();
            $attributeUserGroups = array_merge(
                $attributeUserGroups,
                $this->attributeGroupAccessRepository->getGrantedUserGroups($attributeGroup, Attributes::EDIT_ATTRIBUTES)
            );
            $this->objectDetacher->detach($attribute);
        }

        foreach ($productUserGroups as $userGroup) {
            if ('All' === $userGroup->getName()) {
                return $attributeUserGroups;
            }
        }

        $results = [];
        foreach ($productUserGroups as $productUserGroup) {
            foreach ($attributeUserGroups as $attributeUserGroup) {
                if ($attributeUserGroup->getName() === $productUserGroup->getName()) {
                    $results[] = $attributeUserGroup;
                }
            }
        }

        return $results;
    }
}
