<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\DTO;

use Akeneo\Connectivity\Connection\Domain\Webhook\DTO\UrlReachabilityStatus;
use PhpSpec\ObjectBehavior;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlReachabilityStatusSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(true, 'Lorem ipsum dolor sit amet');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UrlReachabilityStatus::class);
    }

    public function it_returns_success(): void
    {
        $this->success()->shouldReturn(true);
    }

    public function it_returns_message(): void
    {
        $this->message()->shouldReturn('Lorem ipsum dolor sit amet');
    }

    public function it_normalizes(): void
    {
        $this->normalize()->shouldReturn(
            [
                'success' => true,
                'message' => 'Lorem ipsum dolor sit amet',
            ]
        );
    }
}
