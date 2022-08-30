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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\SimpleSelectUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use PhpSpec\ObjectBehavior;

class SimpleSelectUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SimpleSelectUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_set_simple_select_value_object(
        AttributeTarget $attributeTarget,
    ) {
        $attributeTarget->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attributeTarget->getCode()->willReturn('an_attribute_code');
        $attributeTarget->getChannel()->willReturn(null);
        $attributeTarget->getLocale()->willReturn(null);

        $expected = new SetSimpleSelectValue(
            'an_attribute_code',
            null,
            null,
            'a_value',
        );

        $this->create($attributeTarget, new StringValue('a_value'))->shouldBeLike($expected);
    }

    public function it_only_supports_simple_select_target_and_string_value(
        AttributeTarget $validTarget,
        AttributeTarget $invalidTarget,
    ) {
        $validTarget->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $invalidTarget->getAttributeType()->willReturn('pim_catalog_number');

        $validValue = new StringValue('coucou');
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
