<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Prophecy\Argument;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueHandlerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $this->beConstructedWith(
            $pqbFactory,
            $productUpdater,
            $productSaver,
            $objectDetacher,
            $paginatorFactory
        );
    }

    function it_executes_the_update_operation_with_a_configuration(
        $productUpdater,
        $productSaver,
        $paginatorFactory,
        $cursor,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $configuration = [
            'filters' =>
                [
                    [
                        'field'    => 'sku',
                        'operator' => 'IN',
                        'value'    => ['1000', '1001']
                    ]
                ],
            'actions' =>
                [
                    [
                        'field' => 'categories',
                        'value' => ['office', 'bedroom']
                    ]
                ]
        ];

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $productUpdater->addData($product1, 'categories', ['office', 'bedroom'])->shouldBeCalled();
        $productUpdater->addData($product2, 'categories', ['office', 'bedroom'])->shouldBeCalled();

        $productSaver->saveAll([$product1, $product2], Argument::type('array'))->shouldBeCalled();

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
