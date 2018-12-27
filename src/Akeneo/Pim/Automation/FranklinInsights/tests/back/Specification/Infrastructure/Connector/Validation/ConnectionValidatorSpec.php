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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Validator\ConnectionValidator as AppConnectionValidator;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation\ConnectionValidator;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConnectionValidatorSpec extends ObjectBehavior
{
    public function let(AppConnectionValidator $connectionValidator): void
    {
        $this->beConstructedWith($connectionValidator);
    }

    public function it_is_a_validator(): void
    {
        $this->shouldImplement(ValidatorInterface::class);
    }

    public function it_is_a_connection_validator(): void
    {
        $this->shouldHaveType(ConnectionValidator::class);
    }

    public function it_throws_an_exception_if_connection_is_not_active($connectionValidator): void
    {
        $connectionValidator->isValid()->willReturn(false);

        $this->shouldThrow(new ValidationException(DataProviderException::authenticationError()->getMessage()))
             ->during('validate', [Argument::any()]);
    }

    public function it_does_nothing_if_connection_is_active($connectionValidator): void
    {
        $connectionValidator->isValid()->willReturn(true);

        $this->validate(Argument::any())->shouldReturn(null);
    }
}
