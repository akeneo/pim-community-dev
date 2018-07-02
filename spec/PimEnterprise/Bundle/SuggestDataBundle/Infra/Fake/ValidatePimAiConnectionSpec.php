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

namespace spec\PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake\ValidatePimAiConnection;
use PimEnterprise\Component\SuggestData\Application\ValidateConnectionInterface;
use PimEnterprise\Component\SuggestData\Command\SaveConfiguration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ValidatePimAiConnectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValidatePimAiConnection::class);
    }

    function it_is_a_connection_valitidy_checker()
    {
        $this->shouldImplement(ValidateConnectionInterface::class);
    }

    public function it_checks_that_a_pim_ai_connectionis_valid()
    {
        $this->validate(
            new SaveConfiguration(
                'code',
                ['token' => 'the-only-valid-token-for-acceptance']
            )
        )->shouldReturn(true);

        $this->validate(
            new SaveConfiguration(
                'code',
                ['token' => 'an-invalid-token-for-acceptance']
            )
        )->shouldReturn(false);

        $this->validate(new SaveConfiguration('code', ['foo' => 'bar']))->shouldReturn(false);
    }
}
