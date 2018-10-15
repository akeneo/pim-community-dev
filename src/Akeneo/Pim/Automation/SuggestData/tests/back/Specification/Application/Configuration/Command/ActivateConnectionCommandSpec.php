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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Configuration\Command\ActivateConnectionCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Configuration\ValueObject\Token;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ActivateConnectionCommandSpec extends ObjectBehavior
{
    public function let(Token $token): void
    {
        $this->beConstructedWith($token);
    }

    public function it_is_a_save_configuration_command(): void
    {
        $this->shouldHaveType(ActivateConnectionCommand::class);
    }

    public function it_returns_a_token($token): void
    {
        $this->token()->shouldReturn($token);
    }
}
