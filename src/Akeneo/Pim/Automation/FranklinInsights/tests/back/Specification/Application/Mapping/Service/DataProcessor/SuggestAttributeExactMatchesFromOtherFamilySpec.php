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


use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectExactMatchAttributeCodeFromOtherFamilyQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SuggestAttributeExactMatchesFromOtherFamilySpec extends ObjectBehavior
{
    public function let(
        SelectExactMatchAttributeCodeFromOtherFamilyQuery $selectExactMatchAttributeCodeFromOtherFamilyQuery
    ): void
    {
        $this->beConstructedWith($selectExactMatchAttributeCodeFromOtherFamilyQuery);
    }

    public function it_suggests_attributes_from_other_family(
        SelectExactMatchAttributeCodeFromOtherFamilyQuery $selectExactMatchAttributeCodeFromOtherFamilyQuery
    ): void
    {
        $familyCode = new FamilyCode('family_code');
        $pendingAttributesFranklinLabels = ['Color', 'Weight'];
        $matchedPimAttributeCodes = ['Color' => 'color', 'Weight' => null];

        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $expectedAttributeMappingCollection = new AttributeMappingCollection();
        $expectedAttributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', 'color', AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $selectExactMatchAttributeCodeFromOtherFamilyQuery
            ->execute($familyCode, $pendingAttributesFranklinLabels)
            ->willReturn($matchedPimAttributeCodes);

        $this->process($attributeMappingCollection, $familyCode)->shouldBeLike($expectedAttributeMappingCollection);
    }
}
