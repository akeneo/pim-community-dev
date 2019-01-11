<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Authentication\AuthenticationWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\AuthenticationProvider;
use PhpSpec\ObjectBehavior;

class AuthenticationProviderSpec extends ObjectBehavior
{
    public function let(AuthenticationWebService $api): void
    {
        $this->beConstructedWith($api);
    }

    public function it_is_an_authentication_provider(): void
    {
        $this->shouldHaveType(AuthenticationProvider::class);
        $this->shouldImplement(AuthenticationProviderInterface::class);
    }

    public function it_authenticates_using_a_token($api): void
    {
        $goodToken = new Token('true');
        $wrongToken = new Token('wrong');
        $api->authenticate($goodToken)->willReturn(true);
        $api->authenticate($wrongToken)->willReturn(false);

        $this->authenticate($goodToken)->shouldReturn(true);
        $this->authenticate($wrongToken)->shouldReturn(false);
    }
}
