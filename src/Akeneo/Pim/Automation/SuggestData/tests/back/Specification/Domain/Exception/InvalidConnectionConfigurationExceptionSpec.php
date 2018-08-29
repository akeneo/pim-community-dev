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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidConnectionConfigurationException;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\SuggestDataException;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InvalidConnectionConfigurationExceptionSpec extends ObjectBehavior
{
    public function it_is_an_invalid_connection_configuration_exception()
    {
        $this->shouldBeAnInstanceOf(InvalidConnectionConfigurationException::class);
    }

    public function it_is_an_exception()
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }
}
