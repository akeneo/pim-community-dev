<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppUrlGeneratorSpec extends ObjectBehavior
{
    public function let(PimUrl $pimUrl): void
    {
        $this->beConstructedWith($pimUrl);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AppUrlGenerator::class);
    }

    public function it_generates_app_query_parameters(
        PimUrl $pimUrl
    ): void {
        $pimUrl->getPimUrl()->willReturn('http://my-akeneo.test');

        $this->getAppQueryParameters()->shouldReturn(['pim_url' => 'http://my-akeneo.test']);
    }
}
