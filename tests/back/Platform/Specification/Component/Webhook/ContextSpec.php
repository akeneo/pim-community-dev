<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\Webhook\Context;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ContextSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('username_0000', 10, true);
    }

    public function it_is_a_context(): void
    {
        $this->shouldBeAnInstanceOf(Context::class);
    }

    public function it_returns_a_username(): void
    {
        $this->getUsername()->shouldReturn('username_0000');
    }

    public function it_returns_a_user_id(): void
    {
        $this->getUserId()->shouldReturn(10);
    }

    public function it_returns_a_uuid_usage_status(): void
    {
        $this->isUsingUuid()->shouldReturn(true);
    }
}
