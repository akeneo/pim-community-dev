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

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentFactoryInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class UserIntentRegistrySpec extends ObjectBehavior
{
    public function it_returns_the_first_supported_user_intent_factory(
        TargetInterface $target,
        ValueInterface $value,
        UserIntentFactoryInterface $supportedUserIntentFactory,
        UserIntentFactoryInterface $unsupportedUserIntentFactory,
        UserIntentFactoryInterface $anotherSupportedUserIntentFactory,
    ) {
        $unsupportedUserIntentFactory->supports($target, $value)->willReturn(false);
        $supportedUserIntentFactory->supports($target, $value)->willReturn(true);
        $anotherSupportedUserIntentFactory->supports($target, $value)->willReturn(true);
        $target->getActionIfEmpty()->willReturn(TargetInterface::IF_EMPTY_CLEAR);

        $this->beConstructedWith([
            $unsupportedUserIntentFactory,
            $supportedUserIntentFactory,
            $anotherSupportedUserIntentFactory,
        ]);

        $this->getUserIntentFactory($target, $value)->shouldReturn($supportedUserIntentFactory);
    }
}
