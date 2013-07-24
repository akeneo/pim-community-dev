<?php

namespace Pim\Bundle\ImportExportBundle;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product exporter
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Exporter
{
    protected $entityManager;
    protected $serializer;

    /**
     * @param EntityManager       $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManager $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer    = $serializer;
    }

    /**
     * Perform product export into a csv file
     *
     * @return JobExecution
     */
    public function export()
    {
        $jobRepository = new JobRepository();

        $productReader = new ORMCursorReader();
        $productReader->setQuery(
            $this->entityManager
                ->getRepository('PimProductBundle:Product')
                ->buildByScope('mobile')
                ->getQuery()
        );

        $productProcessor = new SerializerProcessor($this->serializer);
        $productProcessor->setFormat('csv');

        $productWriter = new FileWriter();
        $productWriter->setPath('/tmp/export'.uniqid().'.csv');

        $step1 = new ItemStep("Mobile product export");
        $step1->setReader($productReader);
        $step1->setProcessor($productProcessor);
        $step1->setWriter($productWriter);

        $simpleJob = new SimpleJob("Product export");
        $simpleJob->setJobRepository($jobRepository);
        $simpleJob->addStep($step1);

        $jobParameters = new JobParameters();

        $jobLauncher = new SimpleJobLauncher();
        $jobLauncher->setJobRepository($jobRepository);

        return $jobLauncher->run($simpleJob, $jobParameters);
    }
}

