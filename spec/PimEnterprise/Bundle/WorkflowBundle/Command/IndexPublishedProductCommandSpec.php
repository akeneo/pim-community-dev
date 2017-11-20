<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Command;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use PimEnterprise\Bundle\WorkflowBundle\Command\IndexPublishedProductCommand;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\PublishedProductRepository;
use PimEnterprise\Component\Workflow\Model\PublishedProduct;
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
    function it_is_initializable()
    {
        $this->shouldHaveType(IndexPublishedProductCommand::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee:published-product:index');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    function it_indexes_all_published_roducts(
        ContainerInterface $container,
        PublishedProductRepository $publishedProductRepository,
        ProductIndexer $productIndexer,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        PublishedProduct $publishedProduct1,
        PublishedProduct $publishedProduct2,
        PublishedProduct $publishedProduct3,
        PublishedProduct $publishedProduct4
    ) {
        $container->get('pimee_workflow.repository.published_product')->willReturn($publishedProductRepository);
        $container->get('pim_catalog.elasticsearch.published_product_indexer')->willReturn($productIndexer);

        $publishedProductRepository->countAll()->willReturn(4);
        $publishedProductRepository->searchAfter(null, 2)->willReturn([
            $publishedProduct1,
            $publishedProduct2,
        ]);
        $publishedProductRepository->searchAfter($publishedProduct2, 2)->willReturn([
            $publishedProduct3,
            $publishedProduct4,
        ]);

        $publishedProductRepository->searchAfter($publishedProduct4, 2)->willReturn([]);

        $output->writeln('<info>4 published products to index</info>')->shouldBeCalled();
        $output->writeln('Indexing published products 1 to 2')->shouldBeCalled();
        $output->writeln('Indexing published products 3 to 4')->shouldBeCalled();
        $output->writeln('<info>4 published products indexed</info>')->shouldBeCalled();

        $commandInput = new ArrayInput([
            'command'  => 'pimee:published-product:index',
            'page-size' => 2,
            'nodebug'  => true,
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
        $input->getOption('page-size')->willReturn(2);
        $this->run($input, $output);
        $this->shouldHaveType(IndexPublishedProductCommand::class);
    }
}
