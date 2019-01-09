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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class TokenSpec extends ObjectBehavior
{
    public function it_is_a_token(): void
    {
        $this->beConstructedWith('foo');
        $this->beAnInstanceOf(Token::class);
    }

    public function it_returns_the_token_string(): void
    {
        $this->beConstructedWith('bar');
        $this->__toString()->shouldReturn('bar');
    }
}
