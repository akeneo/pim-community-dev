<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\AttributeGroupCompletenessCalculator;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\AttributeGroupCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class AttributeGroupCompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        ValueCompleteCheckerInterface $productValueChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        FamilyInterface $family,
        ProductInterface $product,
        ChannelInterface $projectChannel,
        LocaleInterface $projectLocale,
        ValueInterface $skuValue,
        ValueInterface $nameValue,
        ValueInterface $weightValue,
        ValueInterface $heightValue,
        ValueInterface $documentationValue,
        AttributeInterface $skuAttribute,
        AttributeInterface $nameAttribute,
        AttributeInterface $weightAttribute,
        AttributeInterface $heightAttribute,
        AttributeInterface $documentationAttribute,
        AttributeGroupInterface $general,
        AttributeGroupInterface $marketing,
        AttributeGroupInterface $media
    ) {
        $this->beConstructedWith(
            $productValueChecker,
            $familyRequirementRepository,
            $attributeRepository
        );

        $projectChannel->getCode()->willReturn('ecommerce');
        $projectLocale->getCode()->willReturn('en_US');

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('camcorder');

        $skuValue->isScopable()->willReturn(false);
        $skuValue->isLocalizable()->willReturn(false);
        $nameValue->isScopable()->willReturn(false);
        $nameValue->isLocalizable()->willReturn(false);
        $weightValue->isScopable()->willReturn(false);
        $weightValue->isLocalizable()->willReturn(false);
        $heightValue->isScopable()->willReturn(false);
        $heightValue->isLocalizable()->willReturn(false);
        $documentationValue->isScopable()->willReturn(false);
        $documentationValue->isLocalizable()->willReturn(false);

        $skuAttribute->getCode()->willReturn('sku');
        $nameAttribute->getCode()->willReturn('name');
        $weightAttribute->getCode()->willReturn('weight');
        $heightAttribute->getCode()->willReturn('height');
        $documentationAttribute->getCode()->willReturn('documentation');

        $general->getId()->willReturn(40);
        $marketing->getId()->willReturn(33);
        $media->getId()->willReturn(64);

        $skuAttribute->getGroup()->willReturn($general);
        $nameAttribute->getGroup()->willReturn($general);
        $weightAttribute->getGroup()->willReturn($marketing);
        $heightAttribute->getGroup()->willReturn($marketing);
        $documentationAttribute->getGroup()->willReturn($media);

        $skuValue->getAttributeCode()->willReturn('sku');
        $nameValue->getAttributeCode()->willReturn('name');
        $weightValue->getAttributeCode()->willReturn('weight');
        $heightValue->getAttributeCode()->willReturn('height');
        $documentationValue->getAttributeCode()->willReturn('documentation');

        $attributeRepository->findOneByIdentifier('sku')->willReturn($skuAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weightAttribute);
        $attributeRepository->findOneByIdentifier('height')->willReturn($heightAttribute);
        $attributeRepository->findOneByIdentifier('documentation')->willReturn($documentationAttribute);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupCompletenessCalculator::class);
    }

    function it_is_a_calculator()
    {
        $this->shouldImplement(ProjectItemCalculatorInterface::class);
    }

    function it_pre_processes_completeness_when_all_product_values_are_filled_for_an_attribute_group(
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $projectChannel,
        $projectLocale,
        $skuValue,
        $nameValue,
        $weightValue,
        $heightValue,
        $documentationValue
    ) {
        $product->getValues()->willReturn(
            [
                $skuValue,
                $nameValue,
                $weightValue,
                $heightValue,
                $documentationValue,
            ]
        );

        $productValueChecker->isComplete($skuValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($nameValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($weightValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($heightValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);

        $familyRequirementRepository->findRequiredAttributes($product, $projectChannel, $projectLocale)->willReturn(
            [
                40 => [
                    'sku',
                    'name',
                ],
                33 => [
                    'weight',
                    'height',
                ],
            ]
        );

        $result = $this->calculate($product, $projectChannel, $projectLocale);
        $result->shouldBeArray();
        $result[0]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[1]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[0]->getAttributeGroupId()->shouldReturn(40);
        $result[0]->hasAtLeastOneAttributeFilled()->shouldReturn(0);
        $result[0]->isComplete()->shouldReturn(1);
        $result[1]->getAttributeGroupId()->shouldReturn(33);
        $result[1]->hasAtLeastOneAttributeFilled()->shouldReturn(0);
        $result[1]->isComplete()->shouldReturn(1);
    }

    function it_pre_processes_completeness_when_an_attribute_group_has_at_least_a_product_value_filled(
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $projectChannel,
        $projectLocale,
        $skuValue,
        $documentationValue
    ) {
        $product->getValues()->willReturn(
            [
                $skuValue,
                $documentationValue,
            ]
        );

        $productValueChecker->isComplete($skuValue, $projectChannel, $projectLocale)->willReturn(true);
        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);

        $familyRequirementRepository->findRequiredAttributes($product, $projectChannel, $projectLocale)
            ->willReturn(
                [
                    40 => [
                        'sku',
                        'name',
                    ],
                ]
            );

        $result = $this->calculate($product, $projectChannel, $projectLocale);
        $result->shouldBeArray();
        $result[0]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[0]->getAttributeGroupId()->shouldReturn(40);
        $result[0]->hasAtLeastOneAttributeFilled()->shouldReturn(1);
        $result[0]->isComplete()->shouldReturn(0);
    }

    function it_pre_processes_completeness_when_all_product_values_are_not_filled_for_an_attribute_group(
        $familyRequirementRepository,
        $productValueChecker,
        $product,
        $projectChannel,
        $projectLocale,
        $documentationValue
    ) {
        $product->getValues()->willReturn(
            [
                $documentationValue,
            ]
        );

        $productValueChecker->isComplete($documentationValue, $projectChannel, $projectLocale)->willReturn(true);

        $familyRequirementRepository->findRequiredAttributes($product, $projectChannel, $projectLocale)
            ->willReturn(
                [
                    40 => [
                        'name',
                        'description',
                    ],
                    33 => [
                        'weight',
                        'height',
                    ],
                ]
            );

        $result = $this->calculate($product, $projectChannel, $projectLocale);
        $result->shouldBeArray();
        $result[0]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[1]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[0]->getAttributeGroupId()->shouldReturn(40);
        $result[0]->hasAtLeastOneAttributeFilled()->shouldReturn(0);
        $result[0]->isComplete()->shouldReturn(0);
        $result[1]->getAttributeGroupId()->shouldReturn(33);
        $result[1]->hasAtLeastOneAttributeFilled()->shouldReturn(0);
        $result[1]->isComplete()->shouldReturn(0);
    }
}
