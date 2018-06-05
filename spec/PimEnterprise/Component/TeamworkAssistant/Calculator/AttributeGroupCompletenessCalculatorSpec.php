<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Calculator;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use PimEnterprise\Component\TeamworkAssistant\Calculator\AttributeGroupCompletenessCalculator;
use PimEnterprise\Component\TeamworkAssistant\Calculator\ProjectItemCalculatorInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Repository\FamilyRequirementRepositoryInterface;

class AttributeGroupCompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        ValueCompleteCheckerInterface $productValueChecker,
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
            $familyRequirementRepository
        );

        $projectChannel->getCode()->willReturn('ecommerce');
        $projectLocale->getCode()->willReturn('en_US');

        $product->getFamily()->willReturn($family);
        $family->getCode()->willReturn('camcorder');

        $skuAttribute->isScopable()->willReturn(false);
        $skuAttribute->isLocalizable()->willReturn(false);
        $nameAttribute->isScopable()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(false);
        $weightAttribute->isScopable()->willReturn(false);
        $weightAttribute->isLocalizable()->willReturn(false);
        $heightAttribute->isScopable()->willReturn(false);
        $heightAttribute->isLocalizable()->willReturn(false);
        $documentationAttribute->isScopable()->willReturn(false);
        $documentationAttribute->isLocalizable()->willReturn(false);

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

        $skuValue->getAttribute()->willReturn($skuAttribute);
        $nameValue->getAttribute()->willReturn($nameAttribute);
        $weightValue->getAttribute()->willReturn($weightAttribute);
        $heightValue->getAttribute()->willReturn($heightAttribute);
        $documentationValue->getAttribute()->willReturn($documentationAttribute);
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
        $result[0]->shouldBeAnInstanceOf('PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness');
        $result[1]->shouldBeAnInstanceOf('PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness');
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
        $result[0]->shouldBeAnInstanceOf('PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness');
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
