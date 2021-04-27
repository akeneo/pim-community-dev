<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Promise\ReturnPromise;

class ProductReaderSpec extends ObjectBehavior
{
    function let(ProductQueryBuilderFactoryInterface $pqbFactory, StepExecution $stepExecution)
    {
        $this->beConstructedWith($pqbFactory);

        $this->setStepExecution($stepExecution);
    }

    function it_reads_products(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        StepExecution $stepExecution,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => [
                        'camcorder',
                    ],
                ],
                [
                    'field' => 'completeness',
                    'operator' => '>=',
                    'value' => 100,
                    'context' => [
                        'scope' => 'mobile'
                    ]
                ],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('filters')->willReturn(true);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create()->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('enabled', Operators::EQUALS, true, [])->shouldBeCalled();
        $pqb->addFilter('family', Operators::IN_LIST, ['camcorder'], [])->shouldBeCalled();
        $pqb->addFilter('completeness', Operators::GREATER_OR_EQUAL_THAN, 100, ['scope' => 'mobile'])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $cursor->valid()->willReturn(true, true, true, false);
        $cursor->current()->will(new ReturnPromise([$product1, $product2, $product3]));
        $cursor->next()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);

        $this->initialize();
        $this->read()->shouldReturn($product1);
        $this->read()->shouldReturn($product2);
        $this->read()->shouldReturn($product3);
        $this->read()->shouldReturn(null);
    }

    function it_return_product_count(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        StepExecution $stepExecution,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        JobParameters $jobParameters
    ) {
        $filters = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
                [
                    'field' => 'family',
                    'operator' => 'IN',
                    'value' => [
                        'camcorder',
                    ],
                ],
                [
                    'field' => 'completeness',
                    'operator' => '>=',
                    'value' => 100,
                ],
            ],
            'structure' => [
                'scope' => 'mobile',
                'locales' => ['fr_FR', 'en_US'],
            ],
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('filters')->willReturn(true);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create()->shouldBeCalled()->willReturn($pqb);
        $pqb->addFilter('enabled', Operators::EQUALS, true, [])->shouldBeCalled();
        $pqb->addFilter('family', Operators::IN_LIST, ['camcorder'], [])->shouldBeCalled();
        $pqb->addFilter('completeness', Operators::GREATER_OR_EQUAL_THAN, 100, [])->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $cursor->count()->willReturn(10);

        $this->initialize();
        $this->totalItems()->shouldReturn(10);
    }

    function it_throws_if_the_reader_is_not_initialized()
    {
        $this->shouldThrow(\RuntimeException::class)
            ->during('totalItems');
    }
}
