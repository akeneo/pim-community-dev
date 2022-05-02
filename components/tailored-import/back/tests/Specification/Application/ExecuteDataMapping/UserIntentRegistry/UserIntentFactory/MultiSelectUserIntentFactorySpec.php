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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\MultiSelectUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use PhpSpec\ObjectBehavior;

class MultiSelectUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MultiSelectUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_throws_an_exception_when_target_type_is_invalid(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_textarea');
        $value = new ArrayValue(['f']);

        $this->shouldThrow(new \InvalidArgumentException('The target must be an AttributeTarget and be of type "pim_catalog_multiselect"'))
            ->during('create', [$attributeTarget, $value]);
    }

    public function it_creates_a_set_multi_select_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_multiselect');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getActionIfNotEmpty()->willReturn('set');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetMultiSelectValue(
            'an_attribute_code',
            null,
            null,
            ['a_value']
        );

        $this->create($attributeTarget, new ArrayValue(['a_value']))->shouldBeLike($expected);
    }

    public function it_creates_an_add_multi_select_value_object(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_multiselect');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getActionIfNotEmpty()->willReturn('add');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new AddMultiSelectValue(
            'an_attribute_code',
            null,
            null,
            ['a_value']
        );

        $this->create($attributeTarget, new ArrayValue(['a_value']))->shouldBeLike($expected);
    }

    public function it_supports_target_attribute_type_catalog_multiselect(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_multiselect');

        $this->supports($attributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $attributeTarget
    ) {
        $attributeTarget->getType()->willReturn('pim_catalog_number');

        $this->supports($attributeTarget)->shouldReturn(false);
    }
}
