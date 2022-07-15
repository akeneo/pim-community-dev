<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\UserManagement\Component\Connector\Reader\File\XlsxUserReader;
use PhpSpec\ObjectBehavior;

class XlsxUserReaderSpec extends ObjectBehavior
{
    function let(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($fileIteratorFactory, $converter, []);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_file_reader()
    {
        $this->shouldImplement(FileReaderInterface::class);
        $this->shouldHaveType(XlsxUserReader::class);
    }

    function it_sets_the_avatar_file_path(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParams,
        FileIteratorInterface $fileIterator
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParams);
        $jobParams->get('filePath')->willReturn('/tmp/batch_dir/users.zip');
        $fileIterator->getDirectoryPath()->willReturn('/tmp/batch_dir/users');
        $fileIteratorFactory->create('/tmp/batch_dir/users.zip', [])->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->shouldBeCalled()->willReturn(true);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $fileIterator->current()->shouldBeCalled()->willReturn(
            [
                'julia',
                'files/avatar.png',
            ]
        );
        $fileIterator->getHeaders()->willReturn(['username', 'avatar']);
        $converter->convert(
            ['username' => 'julia', 'avatar' => 'files/avatar.png'],
            []
        )->shouldBeCalled()->willReturn(
            [
                'username' => 'julia',
                'avatar' => [
                    'filePath' => 'files/avatar.png',
                ],
            ]
        );

        $this->read()->shouldReturn(
            [
                'username' => 'julia',
                'avatar' => [
                    'filePath' => '/tmp/batch_dir/users/files/avatar.png',
                ],
            ]
        );
    }
}
