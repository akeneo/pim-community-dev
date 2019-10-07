<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Apps\Infrastructure\Install;

use Akeneo\Apps\Infrastructure\Install\InstallSubscriber;
use Akeneo\Apps\Infrastructure\Installer\AssetsInstaller;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InstallSubscriberSpec extends ObjectBehavior
{
    function let(AssetsInstaller $assetsInstaller)
    {
        $this->beConstructedWith($assetsInstaller);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InstallSubscriber::class);
    }
}
