<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexProductModelCommandSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $bulkProductModelIndexer,
        BulkIndexerInterface $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        Client $productAndProductModelClient
    ) {
        $this->beConstructedWith(
            $productModelRepository,
            $bulkProductModelIndexer,
            $bulkProductModelDescendantsIndexer,
            $objectManager,
            $productAndProductModelClient,
            'akeneo_pim_product_and_product_model'
        );
        $this->setContainer($container);

        $productAndProductModelClient->hasIndex()->willReturn(true);
    }

    function it_has_a_name()
    {
        $this->getDefaultName()->shouldReturn('pim:product-model:index');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_indexes_all_product_models(
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $bulkProductModelIndexer,
        BulkIndexerInterface $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ProductModelInterface $productModel4,
        ProductModelInterface $productModel5,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $productModelRepository->countRootProductModels()->willReturn(5);
        $productModelRepository->searchRootProductModelsAfter(null, 100)->willReturn([$productModel1, $productModel2]);
        $productModelRepository->searchRootProductModelsAfter($productModel2, 100)->willReturn([$productModel3, $productModel4]);
        $productModelRepository->searchRootProductModelsAfter($productModel4, 100)->willReturn([$productModel5]);
        $productModelRepository->searchRootProductModelsAfter($productModel5, 100)->willReturn([]);

        $bulkProductModelIndexer->indexAll([$productModel1, $productModel2], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $bulkProductModelIndexer->indexAll([$productModel3, $productModel4], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $bulkProductModelIndexer->indexAll([$productModel5], ['index_refresh' => Refresh::disable()])->shouldBeCalled();

        $objectManager->clear()->shouldBeCalledTimes(3);

        $output->writeln('<info>5 product models to index</info>')->shouldBeCalled();
        $output->write(Argument::any())->shouldBeCalled();
        $output->writeln('<info>5 product models indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:product-model:index',
            '--all' => true,
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn([]);
        $input->getOption('all')->willReturn(true);
        $this->run($input, $output);
    }

    function it_indexes_a_product_with_identifier(
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $bulkProductModelIndexer,
        BulkIndexerInterface $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModelToIndex,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $productModelRepository->findBy(['code' => ['product_model_code_to_index']])->willReturn([$productModelToIndex]);

        $bulkProductModelIndexer->indexAll([$productModelToIndex], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $objectManager->clear()->shouldBeCalled();

        $output->writeln('<info>1 product models found for indexing</info>')->shouldBeCalled();
        $output->write(Argument::any())->shouldBeCalled();
        $output->writeln('<info>1 product models indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:product-model:index',
            '--codes'    => ['product_model_code_to_index'],
            '--all'      => false,
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_code_to_index']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_indexes_multiple_product_models_with_identifiers(
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $bulkProductModelIndexer,
        BulkIndexerInterface $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $productModelRepository->findBy(['code' => ['product_model_1', 'product_model_2']])
            ->willReturn([$productModel1, $productModel2]);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');

        $bulkProductModelIndexer->indexAll([$productModel1, $productModel2], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $objectManager->clear()->shouldBeCalled();

        $output->writeln('<info>2 product models found for indexing</info>')->shouldBeCalled();
        $output->write(Argument::any())->shouldBeCalled();
        $output->writeln('<info>2 product models indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:product-model:index',
            '--codes'    => ['product_model_1', 'product_model_2'],
            '--all'      => false,
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_1', 'product_model_2']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_non_existing_product_models(
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $bulkProductModelIndexer,
        BulkIndexerInterface $bulkProductModelDescendantsIndexer,
        ObjectManager $objectManager,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $productModelRepository->findBy(['code' => ['product_model_1', 'product_model_2', 'wrong_product_model']])
            ->willReturn([$productModel1, $productModel2]);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');

        $bulkProductModelIndexer->indexAll([$productModel1, $productModel2], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $objectManager->clear()->shouldBeCalled();

        $output->writeln('<error>Some product models were not found for the given codes: wrong_product_model</error>')->shouldBeCalled();
        $output->writeln('<info>2 product models found for indexing</info>')->shouldBeCalled();
        $output->write(Argument::any())->shouldBeCalled();
        $output->writeln('<info>2 product models indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:product-model:index',
            '--codes'    => ['product_model_1', 'product_model_2', 'wrong_product_model'],
            '--all'      => false,
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_1', 'product_model_2', 'wrong_product_model']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_product_models_if_the_all_flag_is_not_set_and_no_identifier_is_passed(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $output->writeln('<error>Please specify a list of product model codes to index or use the flag --all to index all product models</error>')
            ->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'       => 'pim:product-model:index',
            '--identifiers' => [],
            '--all'         => false,
            '--no-debug'    => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn([]);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_throws_an_exception_when_the_product_and_product_model_index_does_not_exist(
        $productAndProductModelClient,
        Application $application,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet,
        InputDefinition $definition,
        OutputFormatter $formatter
    ) {
        $output->isDecorated()->willReturn(true);
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->getFormatter()->willReturn($formatter);

        $productAndProductModelClient->hasIndex()->willReturn(false);

        $commandInput = new ArrayInput([
            'command'    => 'pim:product-model:index',
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);
        $this->setApplication($application);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('identifiers')->willReturn([]);
        $input->getOption('all')->willReturn(true);

        $this->shouldThrow(\RuntimeException::class)->during('run', [$input, $output]);
    }
}
