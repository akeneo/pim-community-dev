<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Marketplace\Model;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\ExtensionList;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ExtensionListSpec extends ObjectBehavior
{
    public function let(Extension $extension)
    {
        $this->beConstructedThrough('create', [12, [$extension]]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ExtensionList::class);
    }

    public function it_returns_a_count()
    {
        $this->count()->shouldBe(12);
    }

    public function it_returns_extensions(Extension $extension)
    {
        $this->extensions()->shouldBe([$extension]);
    }
}
