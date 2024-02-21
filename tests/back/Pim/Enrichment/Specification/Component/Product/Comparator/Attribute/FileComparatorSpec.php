<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\FileComparator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use PhpSpec\ObjectBehavior;

class FileComparatorSpec extends ObjectBehavior
{
    function let(FileInfoRepositoryInterface $repository)
    {
        $this->beConstructedWith(['pim_catalog_file', 'pim_catalog_file'], $repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FileComparator::class);
    }

    function it_finds_a_diff_when_there_was_no_original_file()
    {
        $this->compare(
            ['data' => 'path/to/my/file.txt'],
            ['data' => null]
        )->shouldReturn(['data' => 'path/to/my/file.txt']);
    }

    function it_finds_a_diff_when_file_the_is_deleted($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);
        $repository->findOneByIdentifier(null)->willReturn(null);

        $this->compare(
            ['data' =>  null],
            ['data' => 'key/of/my/original/file.txt']
        )->shouldReturn(['data' => null]);
    }

    function it_finds_a_diff_when_local_file_is_different_from_stored_file($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);
        $repository->findOneByIdentifier(__FILE__)->willReturn(null);

        $this->compare(
            ['data' =>  __FILE__],
            ['data' => 'key/of/my/original/file.txt']
        )->shouldReturn(['data' => __FILE__]);
    }

    function it_finds_a_diff_when_stored_files_are_different($repository, FileInfoInterface $fileInfo, FileInfoInterface $originalFileInfo)
    {
        $originalFileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($originalFileInfo);

        $fileInfo->getHash()->willReturn('different_hash');
        $repository->findOneByIdentifier('d/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png')->willReturn($fileInfo);

        $this->compare(
            ['data' =>  'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png'],
            ['data' => 'key/of/my/original/file.txt']
        )->shouldReturn(['data' =>  'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png']);
    }

    function it_returns_null_when_local_file_equals_stored_file($repository, FileInfoInterface $fileInfo)
    {
        $fileInfo->getHash()->willReturn(sha1_file(__FILE__));
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($fileInfo);
        $repository->findOneByIdentifier(__FILE__)->willReturn(null);

        $this->compare(
            ['data' => __FILE__],
            ['data' => 'key/of/my/original/file.txt']
        )->shouldReturn(null);
    }

    function it_returns_null_when_stored_files_are_equals($repository, FileInfoInterface $fileInfo, FileInfoInterface $originalFileInfo)
    {
        $originalFileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('key/of/my/original/file.txt')->willReturn($originalFileInfo);

        $fileInfo->getHash()->willReturn('hash');
        $repository->findOneByIdentifier('d/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png')->willReturn($fileInfo);

        $this->compare(
            ['data' =>  'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png'],
            ['data' => 'key/of/my/original/file.txt']
        )->shouldReturn(null);
    }

    function it_returns_null_when_filepath_are_equals_and_null()
    {
        $this->compare(
            ['data' => null],
            ['data' => null]
        )->shouldReturn(null);
    }

    function it_returns_null_when_filepath_are_missing()
    {
        $this->compare([], [])->shouldReturn(null);
    }
}
