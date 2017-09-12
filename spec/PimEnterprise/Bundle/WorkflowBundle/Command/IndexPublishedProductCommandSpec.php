<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Command;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\PublishedProductRepository;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexPublishedProductCommandSpec extends ObjectBehavior
{
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim:published-product:index');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_indexes_all_products(
        ContainerInterface $container,
        PublishedProductRepository $productRepository,
        ProductIndexer $productIndexer,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $container->get('pimee_workflow.repository.published_product')->willReturn($productRepository);
        $container->get('pim_catalog.elasticsearch.published_product_indexer')->willReturn($productIndexer);

        $productRepository->countAll()->willReturn(6);
        $productRepository->findAllWithOffsetAndSize(0, 5)->willReturn([]);
        $productRepository->findAllWithOffsetAndSize(5, 5)->willReturn([]);

        $output->writeln('<info>6 published products to index</info>')->shouldBeCalled();
        $output->writeln('Indexing published products 1 to 5')->shouldBeCalled();
        $output->writeln('Indexing published products 6 to 6')->shouldBeCalled();
        $output->writeln('<info>6 published products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command' => 'pim:product:index',
            '--page-size' => 5,
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
        $input->getOption('page-size')->willReturn(5);
        $this->run($input, $output);
    }
}
