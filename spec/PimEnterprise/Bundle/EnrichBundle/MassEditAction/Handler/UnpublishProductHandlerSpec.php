<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Prophecy\Argument;

class UnpublishProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $this->beConstructedWith(
            $pqbFactory,
            $manager,
            $repository,
            $paginatorFactory
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
    }

    function it_executes_a_mass_unpublish_operation_with_a_configuration(
        $manager,
        $repository,
        StepExecution $stepExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => ['55', '66']
                ]
            ],
            'actions' => []
        ];

        $repository->findByIds(['55', '66'])->willReturn([$pubProduct1, $pubProduct2]);

        $manager->unpublish($pubProduct1)->shouldBeCalled();
        $manager->unpublish($pubProduct2)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_executes_a_mass_unpublish_operation_with_a_configuration_and_retrieve_published_from_originals(
        $manager,
        $paginatorFactory,
        $cursor,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1001', '1115']
                ]
            ],
            'actions' => []
        ];
        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $manager->findPublishedProductByOriginal($product1)->willReturn($pubProduct1);
        $manager->findPublishedProductByOriginal($product2)->willReturn($pubProduct2);

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $manager->unpublish($pubProduct1)->shouldBeCalled();
        $manager->unpublish($pubProduct2)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
