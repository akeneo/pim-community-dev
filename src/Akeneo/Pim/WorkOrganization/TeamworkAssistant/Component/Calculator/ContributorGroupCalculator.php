<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\AttributePermissionRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;

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
        $contributors = $this->categoryAccessRepository->getGrantedUserGroupsForEntityWithValues(
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
