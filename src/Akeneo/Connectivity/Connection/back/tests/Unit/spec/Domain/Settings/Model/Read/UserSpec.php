<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\User;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UserSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            42,
            'magento',
            'my_password'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(User::class);
    }

    public function it_returns_the_id(): void
    {
        $this->id()->shouldReturn(42);
    }

    public function it_returns_the_username(): void
    {
        $this->username()->shouldReturn('magento');
    }

    public function it_returns_the_password(): void
    {
        $this->password()->shouldReturn('my_password');
    }
}
