<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Cursor\Cursor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Connector\Model\JobConfigurationInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;

class FilteredProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        DoctrineJobRepository $jobRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $jobRepository,
            $jobConfigurationRepo,
            'update_product_value'
        );
        $this->setConfiguration(['filters' => [], 'actions' => []]);
    }

    function it_reads_products(
        $entityManager,
        $jobRepository,
        JobInstance $jobInstance,
        JobExecution $jobExecution,
        ProductQueryBuilderFactory $pqbFactory,
        ProductQueryBuilder $pqb,
        StepExecution $stepExecution,
        Cursor $cursor,
        ProductInterface $product,
        EntityRepository $customEntityRepository
    ) {
        $jobRepository->getJobManager()->willReturn($entityManager);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $customEntityRepository->findOneBy(['code' => 'update_product_value'])->willReturn($jobInstance);

        $jobInstance->getJobExecutions()->willReturn(new ArrayCollection([$jobExecution]));
        $pqbFactory->create()->willReturn($pqb);
        $pqb->execute()->willReturn($cursor);
        $cursor->next()->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);
        $cursor->current()->willReturn($product);

        $this->read()->shouldReturn($product);
    }
}
