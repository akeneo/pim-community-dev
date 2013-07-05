<?php
namespace Pim\Bundle\ImportExportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\ImportExportBundle\Job\SimpleJob;
use Pim\Bundle\ImportExportBundle\Job\JobParameters;
use Pim\Bundle\ImportExportBundle\Job\JobRepository;
use Pim\Bundle\ImportExportBundle\Job\Launch\SimpleJobLauncher;

use Pim\Bundle\ImportExportBundle\Item\Support\ArrayReader;
use Pim\Bundle\ImportExportBundle\Item\Support\UcfirstProcessor;
use Pim\Bundle\ImportExportBundle\Item\Support\EchoWriter;

use Pim\Bundle\ImportExportBundle\Step\ItemStep;

class ImportExportCommand extends ContainerAwareCommand
{
    /**
     * @{inherit}
     */
    protected function configure()
    {
        $this
            ->setName('ie:test:test')
            ->setDescription('Test import expor infrastructure');
    }

    /**
     * @{inherit}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dummyJobRepository = new JobRepository();

        $itemReader = new ArrayReader();
        $itemReader->setItems(array('hello', 'world', 'akeneo', 'is', 'great'));
        $itemProcessor = new UcfirstProcessor();
        $itemWriter = new EchoWriter();

        $step1 = new ItemStep("My simple step");

        $step1->setReader($itemReader);
        $step1->setProcessor($itemProcessor);
        $step1->setWriter($itemWriter);

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
