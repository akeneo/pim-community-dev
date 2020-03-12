<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition\InMemoryGetExistingAttributeOptionCodes;
use AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition\InMemoryRuleDefinitionRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryGetExistingAttributeOptionCodesSpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetExistingAttributeOptionCodes::class);
    }

    function it_implements_get_existing_attribute_option_codes_interface()
    {
        $this->shouldBeAnInstanceOf(GetExistingAttributeOptionCodes::class);
    }

    function it_returns_attribute_option_codes(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $attribute1 = new Attribute();
        $attribute1->setCode('attribute_code1');
        $attribute2 = new Attribute();
        $attribute2->setCode('attribute_code2');
        $attribute3 = new Attribute();
        $attribute3->setCode('attribute_code3');

        $attributeOption1 = new AttributeOption();
        $attributeOption1->setCode('option_code1');
        $attributeOption1->setAttribute($attribute1);
        $attributeOption2 = new AttributeOption();
        $attributeOption2->setCode('option_code2');
        $attributeOption2->setAttribute($attribute1);
        $attributeOption3 = new AttributeOption();
        $attributeOption3->setCode('option_code3');
        $attributeOption3->setAttribute($attribute1);
        $attributeOption4 = new AttributeOption();
        $attributeOption4->setCode('option_code4');
        $attributeOption4->setAttribute($attribute3);

        $attributeOptionRepository->findAll()->willReturn([
            $attributeOption1,
            $attributeOption2,
            $attributeOption3,
            $attributeOption4,
        ]);

        $this->fromOptionCodesByAttributeCode([
            'attribute_code1' => ['option_code1', 'option_code2'],
            'attribute_code2' => ['option_code3'],
            'attribute_code3' => ['option_code4'],
        ])->shouldReturn([
            'attribute_code1' => ['option_code1', 'option_code2'],
            'attribute_code3' => ['option_code4'],
        ]);
    }

    function it_returns_empty_array_when_no_attribute_option_is_found(
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $attributeOptionRepository->findAll()->willReturn([]);

        $this->fromOptionCodesByAttributeCode([
            'attribute_code1' => ['option_code1', 'option_code2'],
            'attribute_code2' => ['option_code3'],
        ])->shouldReturn([]);
    }
}
