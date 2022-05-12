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
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\AttributeGroupCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeGroupCompletenessCalculator implements ProjectItemCalculatorInterface
{
    /** @var FamilyRequirementRepositoryInterface */
    protected $familyRequirementRepository;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    public function __construct(
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        CompletenessCalculator $completenessCalculator
    ) {
        $this->familyRequirementRepository = $familyRequirementRepository;
        $this->completenessCalculator = $completenessCalculator;
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

        $completenesses = $this->completenessCalculator->fromProductIdentifier($product->getIdentifier());
        $completeness = $completenesses->getCompletenessForChannelAndLocale($channel->getCode(), $locale->getCode());
        if (null === $completeness) {
            return [];
        }

        $result = [];
        foreach ($requiredAttributesPerGroup as $attributeGroupId => $requiredAttributeCodes) {
            $missingRequiredAttributeCodes = array_intersect($requiredAttributeCodes, $completeness->missingAttributeCodes());

            if (empty($missingRequiredAttributeCodes)) {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 0, 1);
            } elseif ($missingRequiredAttributeCodes === $requiredAttributeCodes) {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 0, 0);
            } else {
                $result[] = new AttributeGroupCompleteness($attributeGroupId, 1, 0);
            }
        }

        return $result;
    }
}
