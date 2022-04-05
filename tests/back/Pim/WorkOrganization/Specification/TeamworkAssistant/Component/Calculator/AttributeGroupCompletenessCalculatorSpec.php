<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\AttributeGroupCompletenessCalculator;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Calculator\ProjectItemCalculatorInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\AttributeGroupCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AttributeGroupCompletenessCalculatorSpec extends ObjectBehavior
{
    function let(
        FamilyRequirementRepositoryInterface $familyRequirementRepository,
        CompletenessCalculator $completenessCalculator
    ) {
        $completenessCalculator->fromProductIdentifier('foo')->willReturn(
            new ProductCompletenessWithMissingAttributeCodesCollection(
                42,
                [
                    new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['description', 'picture', 'price'])
                ]
            )
        );
        $this->beConstructedWith(
            $familyRequirementRepository,
            $completenessCalculator
        );
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
        FamilyRequirementRepositoryInterface $familyRequirementRepository
    ) {
        $product = (new Product())->setIdentifier('foo');
        $projectChannel = (new Channel())->setCode('ecommerce');
        $projectLocale = (new Locale())->setCode('en_US');

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
        FamilyRequirementRepositoryInterface $familyRequirementRepository
    ) {
        $product = (new Product())->setIdentifier('foo');
        $projectChannel = (new Channel())->setCode('ecommerce');
        $projectLocale = (new Locale())->setCode('en_US');

        $familyRequirementRepository->findRequiredAttributes($product, $projectChannel, $projectLocale)
            ->willReturn(
                [
                    40 => [
                        'sku',
                        'description',
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
        FamilyRequirementRepositoryInterface $familyRequirementRepository
    ) {
        $product = (new Product())->setIdentifier('foo');
        $projectChannel = (new Channel())->setCode('ecommerce');
        $projectLocale = (new Locale())->setCode('en_US');

        $familyRequirementRepository->findRequiredAttributes($product, $projectChannel, $projectLocale)
            ->willReturn(
                [
                    40 => [
                        'name',
                        'description',
                    ],
                    33 => [
                        'picture',
                        'price',
                    ],
                ]
            );

        $result = $this->calculate($product, $projectChannel, $projectLocale);
        $result->shouldBeArray();
        $result[0]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[1]->shouldBeAnInstanceOf(AttributeGroupCompleteness::class);
        $result[0]->getAttributeGroupId()->shouldReturn(40);
        $result[0]->hasAtLeastOneAttributeFilled()->shouldReturn(1);
        $result[0]->isComplete()->shouldReturn(0);
        $result[1]->getAttributeGroupId()->shouldReturn(33);
        $result[1]->hasAtLeastOneAttributeFilled()->shouldReturn(0);
        $result[1]->isComplete()->shouldReturn(0);
    }
}
