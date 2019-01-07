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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationSpec extends ObjectBehavior
{
    public function it_is_configuration(): void
    {
        $this->shouldHaveType(Configuration::class);
    }

    public function it_sets_and_gets_a_token(): void
    {
        $token = new Token('foo');
        $this->setToken($token);

        $token = $this->getToken();
        $token->shouldBeAnInstanceOf(Token::class);
        $token->__toString()->shouldBeEqualTo('foo');
    }
}
