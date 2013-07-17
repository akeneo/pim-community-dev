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
use Pim\Bundle\BatchBundle\Item\Support\NoopProcessor;
use Pim\Bundle\BatchBundle\Item\Support\EchoWriter;

use Pim\Bundle\BatchBundle\Step\ItemStep;

use Pim\Bundle\ProductBundle\ImportExport\Reader\ProductReader;
use Pim\Bundle\ProductBundle\ImportExport\Writer\EchoProductWriter;
use Pim\Bundle\ProductBundle\ImportExport\Processor\ProductToArrayProcessor;

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
        $simpleJob = $this->getContainer()->get('pim_batch.my_super_job');

        $dummyJobParameters = new JobParameters();
        $dummyJobRepository = new JobRepository();


        //$itemReader = new ArrayReader();
        //$itemReader->setItems(array('hello', 'world', 'akeneo', 'is', 'great'));
        $productManager = $this->getContainer()->get('pim_product.manager.product');
        $itemReader = new ProductReader($productManager);
        //$itemProcessor = new UcfirstProcessor();
        //$itemProcessor = new NoopProcessor();
        $itemProcessor = new ProductToArrayProcessor();
        //$itemWriter = new EchoWriter();
        $itemWriter = new EchoProductWriter();

        $step1 = new ItemStep("My simple step");

        $step1->setReader($itemReader);
        $step1->setProcessor($itemProcessor);
        $step1->setWriter($itemWriter);

        //$simpleJob = new SimpleJob("My super job");
        $simpleJob->setJobRepository($dummyJobRepository);
        $simpleJob->addStep($step1);

        $jobLauncher = new SimpleJobLauncher();
        $jobLauncher->setJobRepository($dummyJobRepository);
        $jobExecution = $jobLauncher->run($simpleJob, $dummyJobParameters);


        echo $simpleJob."\n";
        echo $jobExecution."\n";
    }
}
