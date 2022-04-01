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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\NumberUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PhpSpec\ObjectBehavior;

class NumberUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NumberUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_set_number_value_object(
        AttributeTarget $AttributeTarget
    ) {
        $AttributeTarget->getType()->willReturn('pim_catalog_number');
        $AttributeTarget->getCode()->willReturn('an_attribute_code');
        $AttributeTarget->getChannel()->willReturn(null);
        $AttributeTarget->getLocale()->willReturn(null);

        $expectedSetNumberValue = new SetNumberValue(
            'an_attribute_code',
            null,
            null,
            '123.5'
        );

        $this->create($AttributeTarget, '123.5')->shouldBeLike($expectedSetNumberValue);
    }

    public function it_supports_target_attribute_type_catalog_number(
        AttributeTarget $AttributeTarget
    ) {
        $AttributeTarget->getType()->willReturn('pim_catalog_number');

        $this->supports($AttributeTarget)->shouldReturn(true);
    }

    public function it_does_not_support_others_target_attribute_type(
        AttributeTarget $AttributeTarget
    ) {
        $AttributeTarget->getType()->willReturn('pim_catalog_text');

        $this->supports($AttributeTarget)->shouldReturn(false);
    }
}
