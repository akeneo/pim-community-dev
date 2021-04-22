<?php

namespace spec\Akeneo\Tool\Component\FileStorage\Adapter;

use Akeneo\Tool\Component\FileStorage\Adapter\GoogleCloudStorageAdapter;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\ObjectIterator;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use League\Flysystem\AdapterInterface;
use PhpSpec\ObjectBehavior;

class GoogleCloudStorageAdapterSpec extends ObjectBehavior
{
    function let(StorageClient $storageClient, Bucket $bucket)
    {
        $this->beConstructedWith($storageClient, $bucket, 'spec');
    }

    function it_is_a_flysystem_adapter()
    {
        $this->shouldImplement(AdapterInterface::class);
    }

    function it_is_a_google_cloud_storage_adapter()
    {
        $this->shouldHaveType(GoogleCloudStorageAdapter::class);
    }

    function it_lists_the_contents_of_a_bucket_for_given_path(
        Bucket $bucket,
        ObjectIterator $objects,
        StorageObject $file1
    ) {
        $file1->name()->willReturn('spec/some/directory/file1.csv');
        $file1->info()->willReturn(
            [
                'updated' => '2020-09-13T12:26:40.000Z',
                'contentType' => 'text/csv',
                'size' => 1024,
            ]
        );

        $objects->prefixes()->willReturn(['spec/some/directory/files/']);
        $objects->rewind()->shouldBeCalled();
        $objects->valid()->shouldBeCalledTimes(2)->willReturn(true, false);
        $objects->next()->shouldBeCalledOnce();
        $objects->current()->shouldBeCalledOnce()->willReturn($file1);

        $bucket->objects(
            [
                'prefix' => 'spec/some/directory/',
                'delimiter' => '/',
                'includeTrailingDelimiter' => true,
            ]
        )->shouldBeCalled()->willReturn($objects);

        $this->listContents('some/directory')->shouldReturn(
            [
                [
                    'type' => 'file',
                    'dirname' => 'some/directory',
                    'path' => 'some/directory/file1.csv',
                    'timestamp' => 1600000000,
                    'mimetype' => 'text/csv',
                    'size' => 1024,
                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory/files/',
                    'dirname' => 'some/directory',
                    'basename' => 'files',
                    'filename' => 'files',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory',
                    'dirname' => 'some',
                    'basename' => 'directory',
                    'filename' => 'directory',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some',
                    'dirname' => '',
                    'basename' => 'some',
                    'filename' => 'some',
                ],
            ]
        );
    }

    function it_recursively_lists_the_contents_of_a_bucket_for_a_given_path(
        Bucket $bucket,
        ObjectIterator $objects,
        StorageObject $file1,
        StorageObject $file2,
        StorageObject $file3
    ) {
        $file1->name()->willReturn('spec/some/directory/file1.csv');
        $file1->info()->willReturn(
            [
                'updated' => '2020-09-13T12:26:40.000Z',
                'contentType' => 'text/csv',
                'size' => 1024,
            ]
        );
        $file2->name()->willReturn('spec/some/directory/files/123/image.jpg');
        $file2->info()->willReturn(
            [
                'updated' => '2020-09-13T12:26:40.000Z',
                'contentType' => 'image/jpeg',
                'size' => 55555,
            ]
        );
        $file3->name()->willReturn('spec/some/directory/files/abc/media.png');
        $file3->info()->willReturn(
            [
                'updated' => '2020-09-13T12:26:40.000Z',
                'contentType' => 'image/png',
                'size' => 48524,
            ]
        );

        $objects->rewind()->shouldBeCalled();
        $objects->valid()->shouldBeCalledTimes(4)->willReturn(true, true, true, false);
        $objects->next()->shouldBeCalledTimes(3);
        $objects->current()->shouldBeCalledTimes(3)->willReturn($file1, $file2, $file3);
        $objects->prefixes()->willReturn([]);

        $bucket->objects(['prefix' => 'spec/some/directory/'])->shouldBeCalled()->willReturn($objects);

        $this->listContents('some/directory', true)->shouldReturn(
            [
                [
                    'type' => 'file',
                    'dirname' => 'some/directory',
                    'path' => 'some/directory/file1.csv',
                    'timestamp' => 1600000000,
                    'mimetype' => 'text/csv',
                    'size' => 1024,
                ],
                [
                    'type' => 'file',
                    'dirname' => 'some/directory/files/123',
                    'path' => 'some/directory/files/123/image.jpg',
                    'timestamp' => 1600000000,
                    'mimetype' => 'image/jpeg',
                    'size' => 55555,
                ],
                [
                    'type' => 'file',
                    'dirname' => 'some/directory/files/abc',
                    'path' => 'some/directory/files/abc/media.png',
                    'timestamp' => 1600000000,
                    'mimetype' => 'image/png',
                    'size' => 48524,

                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory',
                    'dirname' => 'some',
                    'basename' => 'directory',
                    'filename' => 'directory',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some',
                    'dirname' => '',
                    'basename' => 'some',
                    'filename' => 'some',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory/files/123',
                    'dirname' => 'some/directory/files',
                    'basename' => '123',
                    'filename' => '123',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory/files',
                    'dirname' => 'some/directory',
                    'basename' => 'files',
                    'filename' => 'files',
                ],
                [
                    'type' => 'dir',
                    'path' => 'some/directory/files/abc',
                    'dirname' => 'some/directory/files',
                    'basename' => 'abc',
                    'filename' => 'abc',
                ],
            ]
        );
    }
}
