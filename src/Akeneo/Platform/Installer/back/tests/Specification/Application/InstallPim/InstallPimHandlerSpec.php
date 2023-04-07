<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Installer\Application\InstallPim;

use Akeneo\Platform\Installer\Application\InstallPim\InstallFixtures;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallPimHandlerSpec extends ObjectBehavior
{
    public function it_installs_the_pim(InstallFixtures $installFixtures): void
    {
        $installFixtures->minimal()->shouldBeCalled();
        $this->beConstructedWith($installFixtures);
        $this->handle();
    }
}
