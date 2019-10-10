<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\Install;

use Akeneo\Apps\Infrastructure\Install\AssetsInstaller;
use Akeneo\Apps\Infrastructure\Install\InstallSubscriber;
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
