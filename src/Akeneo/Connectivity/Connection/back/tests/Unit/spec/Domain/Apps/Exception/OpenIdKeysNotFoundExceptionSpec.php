<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OpenIdKeysNotFoundExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith();
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(\Exception::class);
        $this->shouldHaveType(OpenIdKeysNotFoundException::class);
    }
}
