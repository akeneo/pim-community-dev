<?php

namespace spec\Pim\Bundle\EnrichBundle\Writer\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Prophecy\Argument;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterProductWriterSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManager $entityManager,
        DoctrineJobRepository $jobRepository
    )
    {
        $this->beConstructedWith(
            $pqbFactory,
            $entityManager,
            $jobRepository,
            'update_product_value'
        );
    }

    function it_reads_products($entityManager, $jobRepository, JobInstance $jobInstance, JobExecution $jobExecution)
    {
        $jobRepository->getJobManager()->willReturn($entityManager);
        $entityManager->findOneByCode('update_product_value')->willReturn($jobInstance);
        $jobInstance->getJobExecutions()->willReturn([$jobExecution]);
        $jobExecution->getConfiguration()->willReturn('');

    }
}
