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
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SuggestAttributeExactMatchesFromOtherFamilySpec extends ObjectBehavior
{
    public function let()
    {

    }

    public function it_suggests_attributes_from_other_family()
    {
        $familyCode = new FamilyCode('family_code');

        $attributeMappingCollection = new AttributeMappingCollection();
        $attributeMappingCollection
            ->addAttribute(new AttributeMapping('color', 'Color', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('weight', 'Weight', 'text', null, AttributeMappingStatus::ATTRIBUTE_PENDING))
            ->addAttribute(new AttributeMapping('size', 'Size', 'text', 'pim_size', AttributeMappingStatus::ATTRIBUTE_ACTIVE))
        ;

        $this->process($attributeMappingCollection, $familyCode)->shouldReturn($attributeMappingCollection);
    }
}
