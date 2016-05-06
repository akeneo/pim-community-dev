<?php

namespace spec\Pim\Component\Catalog\Comparator\Attribute;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileComparatorSpec extends ObjectBehavior
{
    function let(FileInfoRepositoryInterface $repository)
    {
        $this->beConstructedWith(['pim_catalog_file', 'pim_catalog_file'], $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Comparator\Attribute\FileComparator');
    }

    function it_finds_a_diff_when_there_was_no_original_file()
    {
        $this->compare(
            ['data' => ['filePath' => 'path/to/my/file.txt']],
            ['data' => ['filePath' => null]]
        )->shouldReturn(['data' => ['filePath' => 'path/to/my/file.txt']]);
    }

    function it_finds_a_diff_when_file_the_is_deleted($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);

        $this->compare(
            ['data' => ['filePath' => null]],
            ['data' => ['filePath' => 'key/of/my/original/file.txt']]
        )->shouldReturn(['data' => ['filePath' => null]]);
    }

    function it_finds_a_diff_when_files_are_different($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);

        $this->compare(
            ['data' => ['filePath' => __FILE__]],
            ['data' => ['filePath' => 'key/of/my/original/file.txt']]
        )->shouldReturn(['data' => ['filePath' => __FILE__]]);
    }

    function it_returns_null_when_there_is_no_diff($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn(sha1_file(__FILE__));
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);

        $this->compare(
            ['data' => ['filePath' => __FILE__]],
            ['data' => ['filePath' => 'key/of/my/original/file.txt']]
        )->shouldReturn(null);
    }

    function it_returns_null_when_filepath_are_equals()
    {
        $this->compare(
            ['data' => ['filePath' => 'key/of/my/original/file.txt']],
            ['data' => ['filePath' => 'key/of/my/original/file.txt']]
        )->shouldReturn(null);
    }

    function it_returns_null_when_filepath_are_equals_and_null()
    {
        $this->compare(
            ['data' => ['filePath' => null]],
            ['data' => ['filePath' => null]]
        )->shouldReturn(null);

        $this->compare(
            ['data' => null],
            ['data' => null]
        )->shouldReturn(null);

        $this->compare(
            ['data' => ['filePath' => null]],
            ['data' => null]
        )->shouldReturn(null);

        $this->compare(
            ['data' => null],
            ['data' => ['filePath' => null]]
        )->shouldReturn(null);
    }
}
