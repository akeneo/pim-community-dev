<?php

namespace spec\Pim\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexProductCommandSpec extends ObjectBehavior
{
    function let(ContainerInterface $container, Client $productClient, Client $productAndProductModelClient)
    {
        $container->get('akeneo_elasticsearch.client.product')->willReturn($productClient);
        $container->get('akeneo_elasticsearch.client.product_and_product_model')->willReturn($productAndProductModelClient);

        $productClient->hasIndex()->willReturn(true);
        $productAndProductModelClient->hasIndex()->willReturn(true);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim:product:index');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_indexes_all_products(
        $container,
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $productIndexer,
        BulkObjectDetacherInterface $productDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductInterface $product5
    ) {
        $container->get('pim_catalog.repository.product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($productIndexer);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productDetacher);

        $productRepository->countAll()->willReturn(5);
        $productRepository->searchAfter(null, 100)->willReturn([$product1, $product2]);
        $productRepository->searchAfter($product2, 100)->willReturn([$product3, $product4]);
        $productRepository->searchAfter($product4, 100)->willReturn([$product5]);
        $productRepository->searchAfter($product5, 100)->willReturn([]);

        $productIndexer->indexAll([$product1, $product2], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $productIndexer->indexAll([$product3, $product4], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $productIndexer->indexAll([$product5], ['index_refresh' => Refresh::disable()])->shouldBeCalled();

        $productDetacher->detachAll([$product1, $product2])->shouldBeCalled();
        $productDetacher->detachAll([$product3, $product4])->shouldBeCalled();
        $productDetacher->detachAll([$product5])->shouldBeCalled();

        $output->writeln('<info>5 products to index</info>')->shouldBeCalled();
        $output->writeln('Indexing products 1 to 2')->shouldBeCalled();
        $output->writeln('Indexing products 3 to 4')->shouldBeCalled();
        $output->writeln('Indexing products 5 to 5')->shouldBeCalled();
        $output->writeln('<info>5 products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'    => 'pim:product:index',
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
        $input->getArgument('identifiers')->willReturn([]);
        $input->getOption('all')->willReturn(true);
        $this->run($input, $output);
    }

    function it_indexes_a_product_with_identifier(
        $container,
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $productIndexer,
        BulkObjectDetacherInterface $productDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductInterface $productToIndex
    ) {
        $container->get('pim_catalog.repository.product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($productIndexer);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productDetacher);

        $productRepository->findBy(['identifier' => ['product_identifier_to_index']])->willReturn([$productToIndex]);

        $productIndexer->indexAll([$productToIndex], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $productDetacher->detachAll([$productToIndex])->shouldBeCalled();

        $output->writeln('<info>1 products found for indexing</info>')->shouldBeCalled();
        $output->writeln('<info>1 products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'       => 'pim:product:index',
            '--identifiers' => ['product_identifier_to_index'],
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
        $input->getArgument('identifiers')->willReturn(['product_identifier_to_index']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_indexes_multiple_products_with_identifiers(
        $container,
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $productIndexer,
        BulkObjectDetacherInterface $productDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $container->get('pim_catalog.repository.product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($productIndexer);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productDetacher);

        $productRepository->findBy(['identifier' => ['product_1', 'product_2']])->willReturn([$product1, $product2]);

        $productIndexer->indexAll([$product1, $product2], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $productDetacher->detachAll([$product1, $product2])->shouldBeCalled();

        $output->writeln('<info>2 products found for indexing</info>')->shouldBeCalled();
        $output->writeln('<info>2 products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'       => 'pim:product:index',
            '--identifiers' => ['product_1', 'product_2'],
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
        $input->getArgument('identifiers')->willReturn(['product_1', 'product_2']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_non_existing_products(
        $container,
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $productIndexer,
        BulkObjectDetacherInterface $productDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        ProductInterface $productToIndex
    ) {
        $container->get('pim_catalog.repository.product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($productIndexer);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productDetacher);

        $productRepository->findBy(['identifier' => ['product_1', 'wrong_product']])->willReturn([$productToIndex]);

        $productToIndex->getIdentifier()->willReturn('product_1');

        $productIndexer->indexAll([$productToIndex], ['index_refresh' => Refresh::disable()])->shouldBeCalled();
        $productDetacher->detachAll([$productToIndex])->shouldBeCalled();

        $output->writeln('<error>Some products were not found for the given identifiers: wrong_product</error>')->shouldBeCalled();
        $output->writeln('<info>1 products found for indexing</info>')->shouldBeCalled();
        $output->writeln('<info>1 products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'       => 'pim:product:index',
            '--identifiers' => ['product_1', 'wrong_product'],
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
        $input->getArgument('identifiers')->willReturn(['product_1', 'wrong_product']);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_does_not_index_products_if_the_all_flag_is_not_set_and_no_identifier_is_passed(
        $container,
        ProductRepositoryInterface $productRepository,
        BulkIndexerInterface $productIndexer,
        BulkObjectDetacherInterface $productDetacher,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $container->get('pim_catalog.repository.product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.indexer.product')->willReturn($productIndexer);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($productDetacher);

        $output->writeln('<error>Please specify a list of product identifiers to index or use the flag --all to index all products</error>')
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
        $input->getArgument('identifiers')->willReturn([]);
        $input->getOption('all')->willReturn(false);
        $this->run($input, $output);
    }

    function it_throws_an_exception_when_the_product_index_does_not_exist(
        $container,
        $productClient,
        Application $application,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $productClient->hasIndex()->willReturn(false);
        $container->getParameter('product_index_name')->willReturn('foo');

        $commandInput = new ArrayInput([
            'command'    => 'pim:product:index',
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
        $input->getArgument('identifiers')->willReturn([]);
        $input->getOption('all')->willReturn(true);

        $this->shouldThrow(\RuntimeException::class)->during('run', [$input, $output]);
    }

    function it_throws_an_exception_when_the_product_and_product_model_index_does_not_exist(
        $container,
        $productAndProductModelClient,
        Application $application,
        InputInterface $input,
        OutputInterface $output,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $productAndProductModelClient->hasIndex()->willReturn(false);
        $container->getParameter('product_index_name')->willReturn('foo');

        $commandInput = new ArrayInput([
            'command'    => 'pim:product:index',
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
        $input->getArgument('identifiers')->willReturn([]);
        $input->getOption('all')->willReturn(true);

        $this->shouldThrow(\RuntimeException::class)->during('run', [$input, $output]);
    }
}
