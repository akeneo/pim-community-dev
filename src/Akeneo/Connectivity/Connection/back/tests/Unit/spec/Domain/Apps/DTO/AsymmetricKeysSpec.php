<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('create', ['a_public_key', 'a_private_key']);
    }

    public function it_is_an_asymmetric_keys(): void
    {
        $this->shouldHaveType(AsymmetricKeys::class);
    }

    public function it_normalizes_an_asymmetric_keys(): void
    {
        $this->normalize()->shouldReturn([
            AsymmetricKeys::PUBLIC_KEY => 'a_public_key',
            AsymmetricKeys::PRIVATE_KEY => 'a_private_key',
        ]);
    }
}
