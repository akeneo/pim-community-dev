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

use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Repository\FamilyRequirementRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeGroupCompletenessCalculator implements ProjectItemCalculatorInterface
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
     * {@inheritdoc}
     */
    public function calculate(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $requiredAttributes = $this->familyRequirementRepository->findRequiredAttributes(
            $product,
            $channel,
            $locale
        );
        $filledAttributes = $this->findFilledAttributes($product, $channel, $locale);

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
     * @param ChannelInterface $channel
     * @param LocaleInterface $locale
     *
     * @return array
     */
    protected function findFilledAttributes(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $filledAttributes = [];
        foreach ($product->getValues() as $value) {
            $attribute = $value->getAttribute();
            if ($attribute->isScopable() && $value->getScope() !== $channel->getCode() ||
                $attribute->isLocalizable() && $value->getLocale() !== $locale->getCode()
            ) {
                continue;
            }

            if ($this->productValueChecker->isComplete($value, $channel, $locale)) {
                $filledAttributes[$attribute->getGroup()->getId()][] = $attribute->getCode();
            }
        }

        return $filledAttributes;
    }
}
