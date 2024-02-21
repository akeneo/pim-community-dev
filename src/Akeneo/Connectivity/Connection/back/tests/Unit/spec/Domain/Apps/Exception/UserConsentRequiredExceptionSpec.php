<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserConsentRequiredExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('an_app_id', 1234);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(\Exception::class);
        $this->shouldHaveType(UserConsentRequiredException::class);
    }

    public function it_gets_the_app_id(): void
    {
        $this->getAppId()->shouldReturn('an_app_id');
    }

    public function it_gets_the_pim_user_id(): void
    {
        $this->getPimUserId()->shouldReturn(1234);
    }
}
