<?php
namespace Pim\Bundle\BatchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\BatchBundle\Job\SimpleJob;
use Pim\Bundle\BatchBundle\Job\JobParameters;
use Pim\Bundle\BatchBundle\Job\JobRepository;
use Pim\Bundle\BatchBundle\Job\Launch\SimpleJobLauncher;

use Pim\Bundle\BatchBundle\Item\Support\ArrayReader;
use Pim\Bundle\BatchBundle\Item\Support\UcfirstProcessor;
use Pim\Bundle\BatchBundle\Item\Support\EchoWriter;

use Pim\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\BatchBundle\Item\Support\SerializerProcessor;
use Pim\Bundle\ImportExportBundle\Writer\FileWriter;
use Pim\Bundle\ImportExportBundle\Reader\ORMCursorReader;

/**
 * Batch command
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:batch:job')
            ->setDescription('Launch a registered job');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $dummyJobRepository = new JobRepository();

        $productReader = new ORMCursorReader();
        $productReader->setQuery(
            $container
                ->get('doctrine.orm.default_entity_manager')
                ->getRepository('PimProductBundle:Product')
                ->createQueryBuilder('p')
                ->getQuery()
        );

        $productProcessor = new SerializerProcessor($container->get('pim_serializer'));
        $productProcessor->setFormat('csv');

        $productWriter = new FileWriter();
        $productWriter->setPath('/tmp/export'.uniqid().'.csv');

        $step1 = new ItemStep("Product export");
        $step1->setReader($productReader);
        $step1->setProcessor($productProcessor);
        $step1->setWriter($productWriter);

        $simpleJob = new SimpleJob("My super job");
        $simpleJob->setJobRepository($dummyJobRepository);
        $simpleJob->addStep($step1);

        $dummyJobParameters = new JobParameters();

        $jobLauncher = new SimpleJobLauncher();
        $jobLauncher->setJobRepository($dummyJobRepository);
        $jobExecution = $jobLauncher->run($simpleJob, $dummyJobParameters);

        echo $simpleJob."\n";
        echo $jobExecution."\n";
    }
}
