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

namespace spec\Akeneo\Pim\Automation\SuggestData\Bundle\Infra\Fake;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\Fake\ValidatePimAiConnection;
use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfiguration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ValidatePimAiConnectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValidatePimAiConnection::class);
    }

    function it_is_a_connection_validity_checker()
    {
        $this->shouldImplement(ValidateConnectionInterface::class);
    }

    public function it_checks_that_a_pim_ai_connection_is_valid()
    {
        $this->validate(['token' => 'the-only-valid-token-for-acceptance'])->shouldReturn(true);
        $this->validate(['token' => 'an-invalid-token-for-acceptance'])->shouldReturn(false);
        $this->validate(['foo' => 'bar'])->shouldReturn(false);
    }
}
