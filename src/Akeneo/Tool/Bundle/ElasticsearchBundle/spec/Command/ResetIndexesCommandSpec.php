<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Bundle\ElasticsearchBundle\Command\ResetIndexesCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResetIndexesCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResetIndexesCommand::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('akeneo:elasticsearch:reset-indexes');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_resets_all_registered_indexes(
        ContainerInterface $container,
        ClientRegistry $clientRegistry,
        Client $client1,
        Client $client2,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        QuestionHelper $questionHelper
    ) {
        $container->get('akeneo_elasticsearch.registry.clients')->willReturn($clientRegistry);
        $clientRegistry->getClients()->willReturn([$client1, $client2]);

        $client1->resetIndex()->shouldBeCalled();
        $client1->getIndexName()->willReturn('index_1');
        $client1->hasIndex()->willReturn(true);

        $client2->getIndexName()->willReturn('index_2');
        $client2->resetIndex()->shouldBeCalled();
        $client2->hasIndex()->willReturn(true);

        $output->writeln('<info>This action will entirely reset all indexes registered in the PIM.</info>')->shouldBeCalled();
        $helperSet->get('question')->willReturn($questionHelper);
        $questionHelper->ask($input, $output, Argument::cetera())->willReturn(true);

        $output->writeln('<info>Resetting the index: index_1</info>')->shouldBeCalled();
        $output->writeln('<info>Resetting the index: index_2</info>')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $output->writeln('')->shouldBeCalled();
        $output->writeln('<info>All the registered indexes have been successfully reset!</info>')
            ->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->writeln('<info>You can now use the command pim:product:index and pim:product-model:index to start re-indexing your product and product models.</info>')
            ->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:indexes:reset',
            '--all'      => true,
            '--no-debug' => true,
        ]);
        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->run($commandInput, $output)->willReturn(0);
        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $this->run($input, $output);
    }

    function it_can_be_aborted_if_the_user_does_not_confirm(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        QuestionHelper $questionHelper
    ) {
        $output->writeln('<info>This action will entirely reset all indexes registered in the PIM.</info>')
            ->shouldBeCalled();

        $helperSet->get('question')->willReturn($questionHelper);
        $questionHelper->ask($input, $output, Argument::cetera())->willReturn(false);

        $output->writeln('<info>Operation aborted. Nothing has been done.</info>')
            ->shouldBeCalled();

        $container->get('akeneo_elasticsearch.registry.clients')->shouldNotBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:indexes:reset',
            '--all'      => true,
            '--no-debug' => true,
        ]);
        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->run($commandInput, $output)->willReturn(0);
        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $this->run($input, $output);
    }

    function it_shows_an_error_if_an_index_does_not_exists_after_reset(
        ContainerInterface $container,
        ClientRegistry $clientRegistry,
        Client $client1,
        Client $client2,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        QuestionHelper $questionHelper
    ) {
        $container->get('akeneo_elasticsearch.registry.clients')->willReturn($clientRegistry);
        $clientRegistry->getClients()->willReturn([$client1, $client2]);

        $client1->resetIndex()->shouldBeCalled();
        $client1->getIndexName()->willReturn('index_1');
        $client1->hasIndex()->willReturn(true);

        $client2->getIndexName()->willReturn('index_2');
        $client2->resetIndex()->shouldBeCalled();
        $client2->hasIndex()->willReturn(false);

        $output->writeln('<info>This action will entirely reset all indexes registered in the PIM.</info>')->shouldBeCalled();
        $helperSet->get('question')->willReturn($questionHelper);
        $questionHelper->ask($input, $output, Argument::cetera())->willReturn(true);

        $output->writeln('<info>Resetting the index: index_1</info>')->shouldBeCalled();
        $output->writeln('<info>Resetting the index: index_2</info>')->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();

        $output->writeln('<error>Something wrong happened to those indexes:</error>')
            ->shouldBeCalled();
        $output->writeln('- The index "index_2" does not exist in Elasticsearch.')
            ->shouldBeCalled();
        $output->writeln('')->shouldBeCalled();
        $output->writeln('<error>Please check that the Elasticsearch server is up and accessible and try running the operation again.<error>')
            ->shouldBeCalled();


        $output->writeln('<info>All the registered indexes have been successfully reset!</info>')
            ->shouldNotBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:indexes:reset',
            '--all'      => true,
            '--no-debug' => true,
        ]);
        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->run($commandInput, $output)->willReturn(0);
        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $this->run($input, $output);
    }
}
