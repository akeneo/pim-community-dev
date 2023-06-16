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

namespace Specification\Akeneo\Platform\Installer\Infrastructure\UserConfigurationResetter;

use Akeneo\Platform\Installer\Domain\Service\UserConfigurationResetterInterface;
use PhpSpec\ObjectBehavior;

class UserConfigurationResetterSpec extends ObjectBehavior
{
    public function let(
        UserConfigurationResetterInterface $userConfigurationResetter1,
        UserConfigurationResetterInterface $userConfigurationResetter2
    ) {
        $this->beConstructedWith([$userConfigurationResetter1, $userConfigurationResetter2]);
    }

    public function it_call_all_user_configuration_resetter(
        UserConfigurationResetterInterface $userConfigurationResetter1,
        UserConfigurationResetterInterface $userConfigurationResetter2
    ) {
        $userConfigurationResetter1->execute()->shouldBeCalled();
        $userConfigurationResetter2->execute()->shouldBeCalled();

        $this->execute();
    }
}
