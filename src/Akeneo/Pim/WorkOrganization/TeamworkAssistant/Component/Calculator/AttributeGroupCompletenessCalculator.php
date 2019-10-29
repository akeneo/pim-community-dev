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

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\AttributeGroupCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeGroupCompletenessCalculator implements ProjectItemCalculatorInterface
{
    /** @var ValueCompleteCheckerInterface */
    protected $productValueChecker;

    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        ValueCompleteCheckerInterface $productValueChecker,
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->productValueChecker = $productValueChecker;
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->attributeRepository = $attributeRepository;
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
        $requiredAttributesPerGroup = $this->familyRequirementRepository->findRequiredAttributes(
            $product,
            $channel,
            $locale
        );
        $filledAttributesPerGroup = $this->findFilledAttributes($product, $channel, $locale);

        $result = [];
        foreach ($requiredAttributesPerGroup as $attributeGroupId => $requiredAttributes) {
            if (!isset($filledAttributesPerGroup[$attributeGroupId])) {
                $filledAttributesPerGroup[$attributeGroupId] = [];
            }

            $intersection = array_intersect($requiredAttributes, $filledAttributesPerGroup[$attributeGroupId]);
            if ($intersection === $requiredAttributes) {
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
     * @param LocaleInterface  $locale
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
            if ($value->isScopable() && $value->getScopeCode() !== $channel->getCode() ||
                $value->isLocalizable() && $value->getLocaleCode() !== $locale->getCode()
            ) {
                continue;
            }

            if ($this->productValueChecker->isComplete($value, $channel, $locale)) {
                $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

                if (null !== $attribute) {
                    $filledAttributes[$attribute->getGroup()->getId()][] = $attribute->getCode();
                }
            }
        }

        return $filledAttributes;
    }
}
