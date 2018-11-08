<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Item\MassEdit;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class TemporaryFileCleanerSpec extends ObjectBehavior
{
    function it_removes_temporary_files(StepExecution $stepExecution, JobParameters $jobParameters)
    {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('actions')->willReturn([['value' => ['filePath' => '/tmp/testfile.txt']]]);

        fopen("/tmp/testfile.txt", "w");
        $this->execute();
        assert(!file_exists('/tmp/testfile.txt'));
    }
}
