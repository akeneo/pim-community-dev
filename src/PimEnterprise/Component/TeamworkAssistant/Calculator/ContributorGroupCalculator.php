<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Calculator;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\User\Model\GroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\TeamworkAssistant\Repository\AttributePermissionRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\FamilyRequirementRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ContributorGroupCalculator implements ProjectItemCalculatorInterface
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepository;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var AttributePermissionRepositoryInterface */
    protected $attributePermissionRepository;

    /**
     * @param CategoryAccessRepository               $categoryAccessRepository
     * @param FamilyRequirementRepositoryInterface   $familyRequirementRepository
     * @param AttributePermissionRepositoryInterface $attributePermissionRepository
     */
    public function __construct(
        CategoryAccessRepository $categoryAccessRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        AttributePermissionRepositoryInterface $attributePermissionRepository
    ) {
        $this->categoryAccessRepository = $categoryAccessRepository;
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->attributePermissionRepository = $attributePermissionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $productContributorsGroupNames = $this->findUserGroupNamesForProduct($product);
        $attributeContributorGroups = $this->findUserGroupForAttribute($product, $channel);

        if (in_array('All', $productContributorsGroupNames)) {
            return $attributeContributorGroups;
        }

        $contributorGroups = [];
        foreach ($attributeContributorGroups as $attributeUserGroup) {
            if (0 === count($productContributorsGroupNames) && 0 === count($product->getCategories())) {
                $contributorGroups[] = $attributeUserGroup;
            } elseif (
                in_array($attributeUserGroup->getName(), $productContributorsGroupNames, true) ||
                'All' === $attributeUserGroup->getName()
            ) {
                $contributorGroups[] = $attributeUserGroup;
            }
        }

        return array_unique($contributorGroups);
    }

    /**
     * Find contributor group names that can edit a product (category permission).
     *
     * @param ProductInterface $product
     *
     * @return string[]
     */
    protected function findUserGroupNamesForProduct(ProductInterface $product)
    {
        $contributors = $this->categoryAccessRepository->getGrantedUserGroupsForProduct(
            $product,
            Attributes::EDIT_ITEMS
        );

        return array_column($contributors, 'name');
    }

    /**
     * Find contributor groups that can edit at least one product attribute (attribute GroupInterface permission).
     *
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     *
     * @return GroupInterface[]
     */
    protected function findUserGroupForAttribute(ProductInterface $product, ChannelInterface $channel)
    {
        $attributeGroupIdentifiers = $this->familyRequirementRepository->findAttributeGroupIdentifiers(
            $product->getFamily(),
            $channel
        );

        return $this->attributePermissionRepository->findContributorsUserGroups($attributeGroupIdentifiers);
    }
}
