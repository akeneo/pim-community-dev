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

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\TextareaValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class TextareaValueStringifierSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository, ['pim_catalog_textarea']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(TextareaValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['pim_catalog_textarea']);
    }

    function it_stringifies_a_rich_textarea_value_when_target_is_text(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXT);
        $attribute->setWysiwygEnabled(false);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("<p>Hello<br></p><p><strong>This &quot;is&quot; a test</strong></p>");

        $this->stringify($value, ['target_attribute_code' => 'target'])->shouldBe('Hello This "is" a test');
    }

    function it_stringifies_a_rich_textarea_value_when_target_is_rich_textarea(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXTAREA);
        $attribute->setWysiwygEnabled(true);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("<p>Hello<br></p><p><strong>This &quot;is&quot; a test</strong></p>");

        $this->stringify($value, ['target_attribute_code' => 'target'])
            ->shouldBe('<p>Hello<br></p><p><strong>This &quot;is&quot; a test</strong></p>');
    }

    function it_stringifies_a_rich_textarea_value_when_target_is_simple_textarea(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXTAREA);
        $attribute->setWysiwygEnabled(false);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("<p>Hello<br></p><p><strong>This &quot;is&quot; a test</strong></p>");

        $this->stringify($value, ['target_attribute_code' => 'target'])
            ->shouldBe("Hello\nThis \"is\" a test");
    }

    function it_stringifies_a_simple_textarea_value_when_target_is_text(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXT);
        $attribute->setWysiwygEnabled(false);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("Hello\nThis \"is\" a test");

        $this->stringify($value, ['target_attribute_code' => 'target'])->shouldBe('Hello This "is" a test');
    }

    function it_stringifies_a_simple_textarea_value_when_target_is_rich_textarea(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXTAREA);
        $attribute->setWysiwygEnabled(true);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("Hello\nThis \"is\" a test");

        $this->stringify($value, ['target_attribute_code' => 'target'])->shouldBe("Hello<br/>This \"is\" a test");
    }

    function it_stringifies_a_simple_textarea_value_when_target_is_simple_textarea(
        AttributeRepositoryInterface $attributeRepository,
        ValueInterface $value
    ) {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXTAREA);
        $attribute->setWysiwygEnabled(false);
        $attributeRepository->findOneByIdentifier('target')->willReturn($attribute);
        $value->__toString()->willReturn("Hello\nThis \"is\" a test");

        $this->stringify($value, ['target_attribute_code' => 'target'])->shouldBe("Hello\nThis \"is\" a test");
    }
}
