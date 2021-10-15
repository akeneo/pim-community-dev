<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Installer\FixtureInstaller;
use PhpSpec\ObjectBehavior;

final class FixtureInstallerRegistrySpec extends ObjectBehavior
{
    public function it_gives_an_installer_by_its_alias(
        FixtureInstaller $installer1,
        FixtureInstaller $installer2
    ) {
        $this->addInstaller($installer1, 'foo');
        $this->addInstaller($installer2, 'bar');

        $this->getInstaller('foo')->shouldReturn($installer1);
        $this->getInstaller('bar')->shouldReturn($installer2);
    }

    public function it_throws_an_exception_if_there_is_no_installer_for_the_given_alias(FixtureInstaller $installer)
    {
        $this->addInstaller($installer, 'foo');

        $this->shouldThrow(new \Exception('Installer bar not found'))->during('getInstaller', ['bar']);
    }
}
