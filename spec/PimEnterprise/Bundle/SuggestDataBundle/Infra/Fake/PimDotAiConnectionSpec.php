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
use PimEnterprise\Bundle\SuggestDataBundle\Infra\Fake\PimDotAiConnection;
use PimEnterprise\Component\SuggestData\Application\ConnectionIsValidInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class PimDotAiConnectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PimDotAiConnection::class);
    }

    function it_is_a_connection_valitidy_checker()
    {
        $this->shouldImplement(ConnectionIsValidInterface::class);
    }

    public function it_checks_that_a_pim_dot_ai_connectionis_valid()
    {
        $this->isValid(['pim_dot_ai_activation_code' => 'the-only-valid-token-for-acceptance'])->shouldReturn(true);
        $this->isValid(['pim_dot_ai_activation_code' => 'an-invalid-token-for-acceptance'])->shouldReturn(false);
        $this->isValid(['foo' => 'bar'])->shouldReturn(false);
    }
}
