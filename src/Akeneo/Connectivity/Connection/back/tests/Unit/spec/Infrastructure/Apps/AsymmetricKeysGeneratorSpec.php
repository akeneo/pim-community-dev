<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AsymmetricKeysGenerator;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysGeneratorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(__DIR__ . '/openssl.cnf');
    }

    public function it_is_an_asymmetric_keys_generator(): void
    {
        $this->shouldHaveType(AsymmetricKeysGenerator::class);
    }

    public function it_generates_asymmetric_keys(): void
    {
        $this->generate()->shouldBeAnInstanceOf(AsymmetricKeys::class);
    }
}
