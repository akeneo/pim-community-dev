<?php

namespace spec\Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;

class LogKeySpec extends ObjectBehavior
{
    function it_fails_when_log_file_is_empty()
    {
        $this->beConstructedWith(new JobExecution());
        $this->shouldThrow(InvalidArgumentException::class)->duringInstantiation();
    }

    function it_fails_when_log_file_does_not_exist()
    {
        $this->beConstructedWith((new JobExecution())->setLogFile('/does/not/exist'));
        $this->shouldThrow(InvalidArgumentException::class)->duringInstantiation();
    }

    function it_is_a_key_built_from_a_job_execution()
    {
        $importInstance = new JobInstance(null, JobInstance::TYPE_IMPORT, 'csv_import');
        $importExecution = (new JobExecution())
            ->setJobInstance($importInstance)
            ->setLogFile(__FILE__)
        ;

        $this->beConstructedWith($importExecution);

        // normally we should have something like 'import/csv_import/ID/log/LogKeySpec.php'
        // but the ID is created by the ORM, we have no control on ID, no way to set it
        $this->__toString()->shouldBe('import/csv_import//log/LogKeySpec.php');
    }
}
