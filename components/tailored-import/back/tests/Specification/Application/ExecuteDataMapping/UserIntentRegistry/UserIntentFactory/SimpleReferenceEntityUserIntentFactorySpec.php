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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\SimpleReferenceEntityUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SimpleReferenceEntityUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SimpleReferenceEntityUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_text');
        $value = new StringValue('f');

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "akeneo_reference_entity"'))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_create_a_set_simple_reference_entity_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('akeneo_reference_entity');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetSimpleReferenceEntityValue(
            'an_attribute_code',
            null,
            null,
            'a_record_code'
        );

        $this->create($attributeTarget, new StringValue('a_record_code'))->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_akeneo_reference_entity(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('akeneo_reference_entity');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_number');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
