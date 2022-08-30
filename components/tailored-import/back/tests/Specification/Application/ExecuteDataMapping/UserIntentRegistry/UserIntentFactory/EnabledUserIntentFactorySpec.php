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

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactory\EnabledUserIntentFactory;
use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\BooleanValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\NumberValue;
use PhpSpec\ObjectBehavior;

class EnabledUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EnabledUserIntentFactory::class);
    }

    public function it_implements_user_intent_factory_interface()
    {
        $this->shouldBeAnInstanceOf(UserIntentFactoryInterface::class);
    }

    public function it_creates_a_set_enabled_object()
    {
        $propertyTarget = PropertyTarget::create(
            'enabled',
            PropertyTarget::ACTION_SET,
            PropertyTarget::IF_EMPTY_CLEAR,
        );

        $expected = new SetEnabled(true);

        $this->create($propertyTarget, new BooleanValue(true))->shouldBeLike($expected);
    }

    public function it_only_supports_enabled_target_and_boolean_value(
        PropertyTarget $validTarget,
        PropertyTarget $invalidTarget,
    ) {
        $validTarget->getCode()->willReturn('enabled');
        $invalidTarget->getCode()->willReturn('categories');

        $validValue = new BooleanValue(false);
        $invalidValue = new NumberValue('5');

        $this->supports($validTarget, $validValue)->shouldReturn(true);

        $this->supports($invalidTarget, $validValue)->shouldReturn(false);

        $this->supports($validTarget, $invalidValue)->shouldReturn(false);
    }
}
