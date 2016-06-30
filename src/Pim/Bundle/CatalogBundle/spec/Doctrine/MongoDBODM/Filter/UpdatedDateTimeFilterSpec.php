<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\Operators;
use Prophecy\Argument;

class UpdatedDateTimeFilterSpec extends ObjectBehavior
{
    function let(
        QueryBuilder $qb,
        JobInstanceRepository $jobInstanceRepository,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $jobInstanceRepository,
            $jobRepository,
            ['updated'],
            [Operators::SINCE_LAST_N_DAYS, Operators::SINCE_LAST_EXPORT]
        );
        $this->setQueryBuilder($qb);
    }

    function it_adds_a_filter_on_products_updated_since_last_export(
        $qb,
        $jobInstanceRepository,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        \DateTime $startTime
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn($jobInstance);
        $jobRepository->getLastJobExecution($jobInstance, 1)->shouldBeCalled()->willReturn($jobExecution);

        $jobExecution->getStartTime()->willReturn($startTime);
        $startTime->format('Y-m-d H:i:s')->willReturn('2016-08-13 14:00:00');

        $qb->field('normalizedData.updated')->shouldBeCalled()->willReturn($qb);
        $qb->gt('2016-08-13 14:00:00')->shouldBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST EXPORT',
            'csv_product_export',
            null,
            null
        );
    }

    function it_does_not_add_an_updated_since_last_export_filter_if_no_option_given(
        $qb,
        $jobInstanceRepository,
        $jobRepository
    ) {
        $jobInstanceRepository->findOneBy(['code' => 'csv_product_export'])->willReturn(null);
        $jobRepository->getLastJobExecution(Argument::cetera())->shouldNotBeCalled();

        $qb->field(Argument::any())->shouldNotBeCalled();
        $qb->gt(Argument::any())->shouldNotBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST EXPORT',
            'csv_product_export',
            null,
            null
        );
    }

    function it_adds_a_filter_on_products_updated_since_last_n_days($qb)
    {
        $qb->field('normalizedData.updated')->shouldBeCalled()->willReturn($qb);
        $qb->gt(Argument::any())->shouldBeCalled();

        $this->addFieldFilter(
            'updated',
            'SINCE LAST N DAYS',
            30,
            null,
            null
        );
    }

    function it_throws_an_exception_if_value_is_wrong()
    {
        $this
            ->shouldThrow(
                InvalidArgumentException::stringExpected('updated', 'filter', 'updated', 'integer')
            )->during(
                'addFieldFilter',
                [
                    'updated',
                    'SINCE LAST EXPORT',
                    42,
                    null,
                    null,
                ]
            );

        $this
            ->shouldThrow(
                InvalidArgumentException::numericExpected('updated', 'filter', 'updated', 'string')
            )->during(
                'addFieldFilter',
                [
                    'updated',
                    'SINCE LAST N DAYS',
                    'csv_product_export',
                    null,
                    null
                ]
            );
    }
}
