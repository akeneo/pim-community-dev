<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor;


use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ApplyAttributeExactMatchesSpec extends ObjectBehavior
{
    public function let(
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        LoggerInterface $logger
    ): void
    {
        $this->beConstructedWith($selectExactMatchAttributeCodeQuery, $saveAttributesMappingByFamilyHandler, $logger);
    }

    public function it_returns_unmodified_collection_when_family_code_is_null(AttributeMappingCollection $attributeMappingCollection)
    {
        $context = ['familyCode' => null];

        $this->process($attributeMappingCollection, $context)->shouldReturn($attributeMappingCollection);
    }

    public function it_returns_unmodified_collection_when_family_code_is_invalid_type(AttributeMappingCollection $attributeMappingCollection)
    {
        $context = ['familyCode' => 'family_code_as_string'];

        $this->process($attributeMappingCollection, $context)->shouldReturn($attributeMappingCollection);
    }


    public function it_applies_matched_pim_attributes(
        SelectExactMatchAttributeCodeQueryInterface$selectExactMatchAttributeCodeQuery,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
    ) {
        $familyCode = new FamilyCode('family_code');
        $context = ['familyCode' => $familyCode];
        $matchedPimAttributeCodes = ['Color' => 'color', 'Weight' => null];
        $pendingAttributesFranklinLabels = ['Color', 'Weight'];

        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $expectedAttributeMappingCollection = new AttributeMappingCollection();
        $expectedAttributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $selectExactMatchAttributeCodeQuery
            ->execute($familyCode, $pendingAttributesFranklinLabels)
            ->willReturn($matchedPimAttributeCodes);

        $saveAttributesMappingByFamilyHandler
            ->handle(Argument::any())
            ->shouldBeCalled();

        $this->process($attributeMappingCollection, $context)->shouldBeLike($expectedAttributeMappingCollection);
    }

    public function it_applies_matched_pim_attributes_only_for_pending_status(
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
    ) {
        $familyCode = new FamilyCode('router');
        $context = ['familyCode' => $familyCode];
        $matchedPimAttributeCodes = ['Color' => 'color'];
        $pendingAttributesFranklinLabels = ['Color'];

        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $expectedAttributeMappingCollection = new AttributeMappingCollection();
        $expectedAttributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_INACTIVE))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $selectExactMatchAttributeCodeQuery
            ->execute($familyCode, $pendingAttributesFranklinLabels)
            ->willReturn($matchedPimAttributeCodes);

        $saveAttributesMappingByFamilyHandler
            ->handle(Argument::any())
            ->shouldBeCalled();

        $this->process($attributeMappingCollection, $context)->shouldBeLike($expectedAttributeMappingCollection);
    }

    public function it_does_not_apply_matched_pim_attributes_if_the_attribute_is_already_mapped(
        SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
    ) {
        $familyCode = new FamilyCode('router');
        $context = ['familyCode' => $familyCode];
        $matchedPimAttributeCodes = ['Color' => 'color'];
        $pendingAttributesFranklinLabels = ['Color'];

        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('finish', 'Color/finish', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_ACTIVE));

        $selectExactMatchAttributeCodeQuery
            ->execute($familyCode, $pendingAttributesFranklinLabels)
            ->willReturn($matchedPimAttributeCodes);

        $saveAttributesMappingByFamilyHandler
            ->handle(Argument::any())
            ->shouldNotBeCalled();

        $this->process($attributeMappingCollection, $context)->shouldBeLike($attributeMappingCollection);
    }
}
