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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Exception\ConnectionConfigurationException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConnectionConfigurationExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_connection_configuration_exception(): void
    {
        $this->shouldBeAnInstanceOf(ConnectionConfigurationException::class);
    }

    public function it_is_an_exception(): void
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_throws_an_invalid_token_message(): void
    {
        $this->beConstructedThrough('invalidToken');

        $this->getMessage()->shouldReturn('akeneo_franklin_insights.connection.flash.invalid');
    }
}
