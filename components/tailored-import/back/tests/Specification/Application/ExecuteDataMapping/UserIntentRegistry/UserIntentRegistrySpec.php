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

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentRegistry\UserIntentCreatorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetInterface;
use PhpSpec\ObjectBehavior;

class UserIntentRegistrySpec extends ObjectBehavior
{
    public function it_return_the_first_supported_user_intent_creator(
        TargetInterface $target,
        UserIntentCreatorInterface $supportedUserIntentCreator,
        UserIntentCreatorInterface $unsupportedUserIntentCreator,
        UserIntentCreatorInterface $anotherSupportedUserIntentCreator,
    ) {
        $unsupportedUserIntentCreator->supports($target)->willReturn(false);
        $supportedUserIntentCreator->supports($target)->willReturn(true);
        $anotherSupportedUserIntentCreator->supports($target)->willReturn(true);

        $this->beConstructedWith([
            $unsupportedUserIntentCreator,
            $supportedUserIntentCreator,
            $anotherSupportedUserIntentCreator
        ]);

        $this->getUserIntentCreator($target)->shouldReturn($supportedUserIntentCreator);
    }
}
