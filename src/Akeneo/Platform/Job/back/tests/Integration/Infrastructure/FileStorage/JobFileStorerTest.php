<?php

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\FileStorage;

use Akeneo\Platform\Job\Application\LaunchJobInstance\JobFileStorerInterface;
use Akeneo\Platform\Job\Infrastructure\FileStorage\JobFileStorer;
use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

class JobFileStorerTest extends IntegrationTestCase
{
    private JobFileStorer $jobFileStorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jobFileStorer = $this->get(JobFileStorerInterface::class);
    }

    public function test_it_stores_a_job_files(): void
    {
        $jobCode = '1';
        $fileName = 'simple_import.xlsx';
        $fileStream = fopen('php://temp', 'r');

        $expected = '1/simple_import.xlsx';

        $this->assertEquals($expected, $this->jobFileStorer->store($jobCode, $fileName, $fileStream));
    }
}
