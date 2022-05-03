<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\TextUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class TextUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(TextUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_textarea');
        $value = new StringValue('s');

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_text"'))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_create_a_set_text_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_text');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetTextValue(
            'an_attribute_code',
            null,
            null,
            'a_value'
        );

        $this->create($attributeTarget, new StringValue('a_value'))->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_text(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_text');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_number');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
