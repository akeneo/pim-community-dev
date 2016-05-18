<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

class FilteredProductReaderSpec extends ObjectBehavior
{
    function let(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->beConstructedWith($pqbFactory);
    }

    function it_reads_products(
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        ProductQueryBuilderFactory $pqbFactory,
        ProductQueryBuilder $pqb,
        StepExecution $stepExecution,
        Cursor $cursor,
        ProductInterface $product,
        EntityRepository $customEntityRepository,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn([]);

        $customEntityRepository->findOneBy(['code' => 'update_product_value'])->willReturn($jobInstance);

        $jobInstance->getJobExecutions()->willReturn(new ArrayCollection([$jobExecution]));
        $pqbFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->next()->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);
        $cursor->current()->willReturn($product);

        $this->read()->shouldReturn($product);
    }
}
