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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Application\ValidateConnectionInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidConnectionConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidateConnectionInterface $connectionValidator,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->beConstructedWith($connectionValidator, $repository);
    }

    function it_is_a_save_connector_configuration_command_handler()
    {
        $this->shouldHaveType(SaveConfigurationHandler::class);
    }

    function it_updates_an_existing_configuration($connectionValidator, $repository)
    {
        $command = new SaveConfiguration('foobar', ['foo' => 'bar']);
        $configuration = new Configuration('foobar', ['foo' => 'bar']);

        $connectionValidator->validate($command)->willReturn(true);
        $repository->findOneByCode('foobar')->willReturn($configuration);

        $repository->save($configuration)->shouldBeCalled();

        $this->handle($command);
    }

    function it_saves_a_new_connector_configuration($connectionValidator, $repository)
    {
        $command = new SaveConfiguration('foobar', ['foo' => 'bar']);

        $connectionValidator->validate($command)->willReturn(true);
        $repository->findOneByCode('foobar')->willReturn(null);

        $repository->save(new Configuration('foobar', ['foo' => 'bar']))->shouldBeCalled();

        $this->handle($command);
    }

    function it_throws_an_exception_if_configuration_is_invalid($connectionValidator, $repository)
    {
        $command = new SaveConfiguration('foobar', ['bar' => 'baz']);

        $connectionValidator->validate($command)->willReturn(false);

        $repository->findOneByCode(Argument::any())->shouldNotBeCalled();
        $repository->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidConnectionConfiguration::forCode('foobar'))->during('handle', [$command]);
    }
}
