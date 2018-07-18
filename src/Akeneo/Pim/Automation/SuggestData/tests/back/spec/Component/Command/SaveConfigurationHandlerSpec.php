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

use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\Adapter\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider\DataProviderFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Command\SaveConfigurationHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidConnectionConfiguration;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfigurationHandlerSpec extends ObjectBehavior
{
    public function let(
        DataProviderFactory $dataProviderFactory,
        ConfigurationRepositoryInterface $repository
    ) {
        $this->beConstructedWith($dataProviderFactory, $repository);
    }

    function it_is_a_save_connector_configuration_command_handler()
    {
        $this->shouldHaveType(SaveConfigurationHandler::class);
    }

    function it_updates_an_existing_configuration(DataProviderInterface $dataProvider, $dataProviderFactory, $repository)
    {
        $command = new SaveConfiguration('foobar', ['token' => 'bar']);
        $configuration = new Configuration('foobar', ['token' => 'bar']);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(true);
        $repository->findOneByCode('foobar')->willReturn($configuration);

        $repository->save($configuration)->shouldBeCalled();

        $this->handle($command);
    }

    function it_saves_a_new_connector_configuration(DataProviderInterface $dataProvider, $dataProviderFactory, $repository)
    {
        $command = new SaveConfiguration('foobar', ['token' => 'bar']);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(true);
        $repository->findOneByCode('foobar')->willReturn(null);

        $repository->save(new Configuration('foobar', ['token' => 'bar']))->shouldBeCalled();

        $this->handle($command);
    }

    function it_throws_an_exception_if_configuration_is_invalid(DataProviderInterface $dataProvider, $dataProviderFactory)
    {
        $command = new SaveConfiguration('foobar', ['token' => 'bar']);

        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(false);

        $this->shouldThrow(InvalidConnectionConfiguration::forCode('foobar'))->during('handle', [$command]);
    }
}
