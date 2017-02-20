<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class PreProcessAttributeCompletenessEngine implements PreProcessAttributeCompletenessEngineInterface
{
    /** @var ProductValueCompleteCheckerInterface */
    protected $productValueChecker;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /**
     * @param ProductValueCompleteCheckerInterface $productValueChecker
     * @param FamilyRequirementRepositoryInterface $familyRequirementRepository
     */
    public function __construct(
        ProductValueCompleteCheckerInterface $productValueChecker,
        FamilyRequirementRepositoryInterface $familyRequirementRepository
    ) {
        $this->productValueChecker = $productValueChecker;
        $this->familyRequirementRepository = $familyRequirementRepository;
    }

    /**
     * Get the attribute group completeness, it compares the product attributes filled with the attributes
     * required by the family.
     *
     * @param ProjectInterface $project
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getAttributeGroupCompleteness(ProjectInterface $project, ProductInterface $product)
    {
        $requiredAttributes = $this->familyRequirementRepository->findRequiredAttributes($product, $project);
        $filledAttributes = $this->findFilledAttributes($product, $project);

        $result = [];
        foreach ($requiredAttributes as $attributeGroupId => $attributes) {
            if (!isset($filledAttributes[$attributeGroupId])) {
                $filledAttributes[$attributeGroupId] = [];
            }

            $intersection = array_intersect($attributes, $filledAttributes[$attributeGroupId]);
            if ($intersection === $attributes) {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 0, 1);
            } elseif (count($intersection) > 0) {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 1, 0);
            } else {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 0, 0);
            }
        }

        return $result;
    }

    /**
     * Return every filled attributes for a product. This method return the attribute codes indexed by attribute ids:
     *
     * [
     *      40 => [
     *          'sku',
     *          'name',
     *      ],
     *      33 => [
     *          'description',
     *      ],
     * ];
     *
     * @param ProductInterface $product
     * @param ProjectInterface $project
     *
     * @return array
     */
    protected function findFilledAttributes(ProductInterface $product, ProjectInterface $project)
    {
        $filledAttributes = [];
        foreach ($product->getValues() as $value) {
            $attribute = $value->getAttribute();
            if ($attribute->isScopable() && $value->getScope() !== $project->getChannel()->getCode() ||
                $attribute->isLocalizable() && $value->getLocale() !== $project->getLocale()->getCode()
            ) {
                continue;
            }

            if ($this->productValueChecker->isComplete($value, $project->getChannel(), $project->getLocale())) {
                $filledAttributes[$attribute->getGroup()->getId()][] = $attribute->getCode();
            }
        }

        return $filledAttributes;
    }
}
