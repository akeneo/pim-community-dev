<?php

namespace spec\Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexProductModelCommandSpec extends ObjectBehavior
{
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim:product-model:index');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_indexes_all_product_models(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelDescendantsIndexer,
        BulkObjectDetacherInterface $productModelDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $container->get('pim_catalog.repository.product_model')->willReturn($productModelRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productModelDetacher);
        $container->get('pim_catalog.elasticsearch.indexer.product_model')->willReturn($productModelIndexer);
        $container->get('pim_catalog.elasticsearch.indexer.product_model_descendance')
            ->willReturn($productModelDescendantsIndexer);

        $productModelRepository->countRootProductModels()->willReturn(2);
        $productModelRepository
            ->findRootProductModelsWithOffsetAndSize(0, 100)
            ->willReturn([$productModel1, $productModel2]);

        $productModelIndexer->indexAll([$productModel1, $productModel2])->shouldBeCalled();

        $productModelDetacher->detachAll([$productModel1, $productModel2])->shouldBeCalled();

        $output->writeln('<info>2 product models to index</info>')->shouldBeCalled();
        $output->writeln('Indexing product models 1 to 2')->shouldBeCalled();
        $output->writeln('<info>2 product models indexed</info>')->shouldBeCalled();

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
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn([]);
        $input->getOption('all')->willReturn(true);
        $this->run($input, $output);
    }

    function it_indexes_a_product_with_identifier(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelDescendantsIndexer,
        BulkObjectDetacherInterface $productModelDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModelToIndex
    ) {
        $container->get('pim_catalog.repository.product_model')->willReturn($productModelRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productModelDetacher);
        $container->get('pim_catalog.elasticsearch.indexer.product_model')->willReturn($productModelIndexer);
        $container->get('pim_catalog.elasticsearch.indexer.product_model_descendance')
            ->willReturn($productModelDescendantsIndexer);

        $productModelRepository->findBy(['code' => ['product_model_code_to_index']])->willReturn([$productModelToIndex]);

        $productModelIndexer->indexAll([$productModelToIndex])->shouldBeCalled();
        $productModelDetacher->detachAll([$productModelToIndex])->shouldBeCalled();

        $output->writeln('<info>1 product models found for indexing</info>')->shouldBeCalled();
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
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_code_to_index']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_indexes_multiple_product_models_with_identifiers(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelDescendantsIndexer,
        BulkObjectDetacherInterface $productModelDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $container->get('pim_catalog.repository.product_model')->willReturn($productModelRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productModelDetacher);
        $container->get('pim_catalog.elasticsearch.indexer.product_model')->willReturn($productModelIndexer);
        $container->get('pim_catalog.elasticsearch.indexer.product_model_descendance')
            ->willReturn($productModelDescendantsIndexer);

        $productModelRepository->findBy(['code' => ['product_model_1', 'product_model_2']])
            ->willReturn([$productModel1, $productModel2]);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');

        $productModelIndexer->indexAll([$productModel1, $productModel2])->shouldBeCalled();
        $productModelDetacher->detachAll([$productModel1, $productModel2])->shouldBeCalled();

        $output->writeln('<info>2 product models found for indexing</info>')->shouldBeCalled();
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
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_1', 'product_model_2']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_non_existing_product_models(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelDescendantsIndexer,
        BulkObjectDetacherInterface $productModelDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $container->get('pim_catalog.repository.product_model')->willReturn($productModelRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productModelDetacher);
        $container->get('pim_catalog.elasticsearch.indexer.product_model')->willReturn($productModelIndexer);
        $container->get('pim_catalog.elasticsearch.indexer.product_model_descendance')
            ->willReturn($productModelDescendantsIndexer);

        $productModelRepository->findBy(['code' => ['product_model_1', 'product_model_2', 'wrong_product_model']])
            ->willReturn([$productModel1, $productModel2]);

        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getCode()->willReturn('product_model_2');

        $productModelIndexer->indexAll([$productModel1, $productModel2])->shouldBeCalled();
        $productModelDetacher->detachAll([$productModel1, $productModel2])->shouldBeCalled();

        $output->writeln('<error>Some product models were not found for the given codes: wrong_product_model</error>')->shouldBeCalled();
        $output->writeln('<info>2 product models found for indexing</info>')->shouldBeCalled();
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
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn(['product_model_1', 'product_model_2', 'wrong_product_model']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_product_models_if_the_all_flag_is_not_set_and_no_identifier_is_passed(
        ContainerInterface $container,
        ProductModelRepositoryInterface $productModelRepository,
        BulkIndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelDescendantsIndexer,
        BulkObjectDetacherInterface $productModelDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $container->get('pim_catalog.repository.product_model')->willReturn($productModelRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productModelDetacher);
        $container->get('pim_catalog.elasticsearch.indexer.product_model')->willReturn($productModelIndexer);
        $container->get('pim_catalog.elasticsearch.indexer.product_model_descendance')
            ->willReturn($productModelDescendantsIndexer);

        $output->writeln('<error>Please specify a list of product model codes to index or use the flag --all to index all product models</error>')
            ->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'       => 'pim:product:index',
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
        $this->setContainer($container);
        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();
        $input->getArgument('codes')->willReturn([]);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }
}
