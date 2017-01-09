<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

/**
 * Fill in the sql table used to return the project completeness.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PreProcessCompletenessStep implements CalculationStepInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var ProductValueCompleteCheckerInterface */
    protected $productValueChecker;

    public function __construct(
        PreProcessingRepositoryInterface $preProcessingRepository,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        ProductValueCompleteCheckerInterface $productValueChecker
    ) {
        $this->preProcessingRepository = $preProcessingRepository;
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->productValueChecker = $productValueChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $requiredAttributes = $this->familyRequirementRepository->findRequiredAttributes($product, $project);
        $filledAttributes = $this->normalizeProduct($product, $project);
        $attributeGroupCompleteness = $this->getAttributeGroupCompleteness($filledAttributes, $requiredAttributes);

        $this->preProcessingRepository->addAttributeGroupCompleteness(
            $product,
            $project,
            $attributeGroupCompleteness
        );
    }

    /**
     * Get the attribute group completeness, it compares the product attributes filled with the attributes
     * required by the family.
     *
     * @param array $filledAttributes
     * @param array $requiredAttributes
     *
     * @return array
     */
    protected function getAttributeGroupCompleteness(array $filledAttributes, array $requiredAttributes)
    {
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
    protected function normalizeProduct(ProductInterface $product, ProjectInterface $project)
    {
        $filledAttributes = [];
        foreach ($product->getValues() as $value) {
            if ($this->productValueChecker->isComplete($value, $project->getChannel(), $project->getLocale())) {
                $attribute = $value->getAttribute();
                $filledAttributes[$attribute->getGroup()->getId()][] = $attribute->getCode();
            }
        }

        return $filledAttributes;
    }
}
