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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\MultiReferenceEntityUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class MultiReferenceEntityUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(MultiReferenceEntityUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_set_multi_reference_entity_value_object(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('akeneo_reference_entity_collection');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getActionIfNotEmpty()->willReturn('set');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetMultiReferenceEntityValue(
            'an_attribute_code',
            null,
            null,
            ['a_value'],
        );

        $this->create($attributeTarget, new ArrayValue(['a_value']))->shouldBeLike($expected);
    }

    public function it_creates_an_add_multi_reference_entity_value_object(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('akeneo_reference_entity_collection');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getActionIfNotEmpty()->willReturn('add');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new AddMultiReferenceEntityValue(
            'an_attribute_code',
            null,
            null,
            ['a_value'],
        );

        $this->create($attributeTarget, new ArrayValue(['a_value']))->shouldBeLike($expected);
    }

    public function it_only_supports_multi_reference_entity_target_and_array_and_string_values(
        AttributeTarget $validTarget,
        AttributeTarget $invalidTarget,
    ) {
        $validTarget->getAttributeType()->willReturn('akeneo_reference_entity_collection');
        $invalidTarget->getAttributeType()->willReturn('pim_catalog_number');

        $validValue = new ArrayValue(['coucou']);
        $anotherValidValue = new StringValue('coucou');
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);
        $this->supports($validTarget, $anotherValidValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);
        $this->supports($invalidTarget, $anotherValidValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
