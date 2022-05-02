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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\BooleanUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class BooleanUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(BooleanUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_text');
        $value = new BooleanValue(true);

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_boolean"'))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_throws_an_exception_when_value_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_boolean');
        $value = new StringValue('18');

        $this->shouldThrow(new \InvalidArgumentException(sprintf('BooleanUserIntentFactory only supports Boolean value, %s given', StringValue::class)))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_creates_a_set_boolean_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_boolean');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetBooleanValue(
            'an_attribute_code',
            null,
            null,
            true
        );

        $this->create($attributeTarget, new BooleanValue(true))->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_boolean(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_boolean');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_number');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
