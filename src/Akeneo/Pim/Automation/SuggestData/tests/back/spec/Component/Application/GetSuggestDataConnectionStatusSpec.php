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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Application;

use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSuggestDataConnectionStatusSpec extends ObjectBehavior
{
    function let(
        ConfigurationRepositoryInterface $configurationRepository,
        ValidateConnectionInterface $connectionValidator
    ) {
        $this->beConstructedWith($configurationRepository, $connectionValidator);
    }

    function it_checks_that_a_connection_is_active($configurationRepository, $connectionValidator)
    {
        $configuration = new Configuration('foobar', ['foo', 'bar']);

        $configurationRepository->findOneByCode('foobar')->willReturn($configuration);
        $connectionValidator->validate(['foo', 'bar'])->willReturn(true);

        $this->forCode('foobar')->shouldReturn(true);
    }

    function it_checks_that_a_connection_is_inactive($configurationRepository, $connectionValidator)
    {
        $configuration = new Configuration('foobar', ['foo', 'bar']);

        $configurationRepository->findOneByCode('foobar')->willReturn($configuration);
        $connectionValidator->validate(['foo', 'bar'])->willReturn(false);

        $this->forCode('foobar')->shouldReturn(false);
    }

    function it_checks_that_a_connection_does_not_exist($configurationRepository, $connectionValidator)
    {
        $configurationRepository->findOneByCode('foobar')->willReturn(null);
        $connectionValidator->validate(Argument::any())->shouldNotBeCalled();

        $this->forCode('foobar')->shouldReturn(false);
    }
}
