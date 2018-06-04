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

namespace spec\PimEnterprise\Component\SuggestData\Command;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Application\ConnectionIsValidInterface;
use PimEnterprise\Component\SuggestData\Command\SaveConfiguration;
use PimEnterprise\Component\SuggestData\Command\SaveConfigurationHandler;
use PimEnterprise\Component\SuggestData\Exception\InvalidConnectionConfiguration;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationHandlerSpec extends ObjectBehavior
{
    public function let(
        ConnectionIsValidInterface $pimDotAiConnection,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->beConstructedWith($pimDotAiConnection, $repository);
    }

    function it_is_a_save_connector_configuration_command_handler()
    {
        $this->shouldHaveType(SaveConfigurationHandler::class);
    }

    function it_updates_an_existing_configuration($pimDotAiConnection, $repository)
    {
        $command = new SaveConfiguration('foobar', ['foo' => 'bar']);
        $configuration = new Configuration('foobar', ['foo' => 'bar']);

        $pimDotAiConnection->isValid(['foo' => 'bar'])->willReturn(true);
        $repository->find('foobar')->willReturn($configuration);

        $repository->save($configuration)->shouldBeCalled();

        $this->handle($command);
    }

    function it_saves_a_new_connector_configuration($pimDotAiConnection, $repository)
    {
        $command = new SaveConfiguration('foobar', ['foo' => 'bar']);

        $pimDotAiConnection->isValid(['foo' => 'bar'])->willReturn(true);
        $repository->find('foobar')->willReturn(null);

        $repository->save(new Configuration('foobar', ['foo' => 'bar']))->shouldBeCalled();

        $this->handle($command);
    }

    function it_throws_an_exception_if_configuration_is_invalid($pimDotAiConnection, $repository)
    {
        $command = new SaveConfiguration('foobar', ['bar' => 'baz']);

        $pimDotAiConnection->isValid(['bar' => 'baz'])->willReturn(false);

        $repository->find(Argument::any())->shouldNotBeCalled();
        $repository->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidConnectionConfiguration::forCode('foobar'))->during('handle', [$command]);
    }
}
