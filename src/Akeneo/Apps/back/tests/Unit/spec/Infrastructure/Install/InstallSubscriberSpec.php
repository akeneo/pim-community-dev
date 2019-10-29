<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\Install;

use Akeneo\Apps\Infrastructure\Install\AssetsInstaller;
use Akeneo\Apps\Infrastructure\Install\InstallSubscriber;
use Doctrine\DBAL\Driver\Connection;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriberSpec extends ObjectBehavior
{
    function let(AssetsInstaller $assetsInstaller, Connection $dbalConnection)
    {
        $this->beConstructedWith($assetsInstaller, $dbalConnection);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InstallSubscriber::class);
    }
}
