<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Install\AssetsInstaller;
use Akeneo\Connectivity\Connection\Infrastructure\Install\InstallSubscriber;
use Doctrine\DBAL\Driver\Connection as DbalConnection;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallSubscriberSpec extends ObjectBehavior
{
    public function let(AssetsInstaller $assetsInstaller, DbalConnection $dbalConnection)
    {
        $this->beConstructedWith($assetsInstaller, $dbalConnection);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InstallSubscriber::class);
    }
}
