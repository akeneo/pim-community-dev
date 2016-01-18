<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;
use Prophecy\Argument;

class InvalidItemsCsvArchiverSpec extends ObjectBehavior
{
    function let(
        InvalidItemsCollector $collector,
        CsvWriter $writer,
        Filesystem $filesystem,
        LocalAdapter $adapter
    ) {
        $filesystem->getAdapter()->willReturn($adapter);
        $adapter->getPathPrefix()->willReturn(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'archivist/');
        $this->beConstructedWith($collector, $writer, $filesystem, '/root');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\InvalidItemsCsvArchiver');
    }

    function it_doesnt_create_a_file_when_there_are_no_invalid_items(
        InvalidItemsCollector $collector,
        CsvWriter $writer,
        JobExecution $jobExecution
    ) {
        $collector->getInvalidItems()->willReturn(null);
        $writer->initialize()->shouldNotBeCalled();
        $writer->write(Argument::any())->shouldNotBeCalled();
        $writer->flush()->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_archives_unvalid_items(
        InvalidItemsCollector $collector,
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Filesystem $filesystem
    ) {
        $collector->getInvalidItems()->willReturn(['items']);

        $jobExecution->getId()->willReturn('id');
        $jobExecution->getJobInstance()->willReturn($jobInstance);

        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');

        $filesystem->put('type/alias/id/invalid/invalid_items.csv', '')->shouldBeCalled();
        $writer->setFilePath('/tmp/archivist/type/alias/id/invalid/invalid_items.csv')->shouldBeCalled();
        $writer->initialize()->shouldBeCalled();
        $writer->write(['items'])->shouldBeCalled();
        $writer->flush()->shouldBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('invalid');
    }

    function it_returns_true_for_the_supported_job(
        $collector,
        JobExecution $jobExecution
    ) {
        $collector->getInvalidItems()->willReturn(['a' => ['a'], 'b' => ['b'], 'c' => ['c']]);

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        $collector,
        JobExecution $jobExecution
    ) {
        $collector->getInvalidItems()->willReturn(['a' => ['a' => ['b']], 'b' => ['b'], 'c' => ['c']]);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
