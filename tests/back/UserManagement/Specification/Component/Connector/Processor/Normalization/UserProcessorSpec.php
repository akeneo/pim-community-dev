<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Normalization\UserProcessor;
use Akeneo\UserManagement\Component\Model\UserInterface;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserProcessorSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $userNormalizer,
        ObjectDetacherInterface $objectDetacher,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($userNormalizer, $objectDetacher, $filesystemProvider, $fileFetcher);
        $this->setStepExecution($stepExecution);
    }

    function it_is_an_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_a_user_normaliztion_processor()
    {
        $this->shouldHaveType(UserProcessor::class);
    }

    function it_processes_a_user(
        NormalizerInterface $userNormalizer,
        ObjectDetacherInterface $objectDetacher,
        UserInterface $user
    ) {
        $user->getAvatar()->willReturn(null);

        $userNormalizer->normalize($user, 'standard')->shouldBeCalled()->willReturn(['normalized_user']);
        $objectDetacher->detach($user)->shouldBeCalled();

        $this->process($user)->shouldReturn(['normalized_user']);
    }

    function it_fetches_the_avatar_file(
        NormalizerInterface $userNormalizer,
        ObjectDetacherInterface $objectDetacher,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ExecutionContext $context,
        UserInterface $user,
        FilesystemInterface $filesystem,
        FileInfoInterface $avatar
    ) {
        $context->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp/batch_dir/');
        $jobExecution->getExecutionContext()->willReturn($context);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $avatar->getKey()->willReturn('a/b/c/123avatar.png');
        $avatar->getOriginalFilename()->willReturn('avatar.png');
        $avatar->getStorage()->willReturn('catalogStorage');
        $user->getUsername()->willReturn('julia');
        $user->getAvatar()->willReturn($avatar);

        $userNormalizer->normalize($user, 'standard')->shouldBeCalled()->willReturn([
            'username' => 'julia',
            'email' => 'julia@example.com',
            'avatar' => [
                'filePath' => 'a/b/c/123avatar.png',
                'originalFilename' => 'avatar.png',
            ],
        ]);
        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($filesystem);
        $fileFetcher->fetch(
            $filesystem,
            'a/b/c/123avatar.png',
            [
                'filePath' => '/tmp/batch_dir/files/julia/avatar/',
                'filename' => 'avatar.png',
            ]
        )->shouldBeCalled();

        $objectDetacher->detach($user)->shouldBeCalled();

        $this->process($user)->shouldReturn([
            'username' => 'julia',
            'email' => 'julia@example.com',
            'avatar' => [
                'filePath' => 'files/julia/avatar/avatar.png',
                'originalFilename' => 'avatar.png',
            ],
        ]);
    }

    function it_adds_a_warning_if_the_avatar_cannot_be_fetched(
        NormalizerInterface $userNormalizer,
        ObjectDetacherInterface $objectDetacher,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ExecutionContext $context,
        UserInterface $user,
        FilesystemInterface $filesystem,
        FileInfoInterface $avatar
    ) {
        $context->get(JobInterface::WORKING_DIRECTORY_PARAMETER)->willReturn('/tmp/batch_dir/');
        $jobExecution->getExecutionContext()->willReturn($context);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $avatar->getKey()->willReturn('a/b/c/123avatar.png');
        $avatar->getOriginalFilename()->willReturn('avatar.png');
        $avatar->getStorage()->willReturn('catalogStorage');
        $user->getUsername()->willReturn('julia');
        $user->getAvatar()->willReturn($avatar);

        $userNormalizer->normalize($user, 'standard')->shouldBeCalled()->willReturn(
            [
                'username' => 'julia',
                'email' => 'julia@example.com',
                'avatar' => [
                    'filePath' => 'a/b/c/123avatar.png',
                    'originalFilename' => 'avatar.png',
                ],
            ]
        );
        $filesystemProvider->getFilesystem('catalogStorage')->shouldBeCalled()->willReturn($filesystem);
        $fileFetcher->fetch(
            $filesystem,
            'a/b/c/123avatar.png',
            [
                'filePath' => '/tmp/batch_dir/files/julia/avatar/',
                'filename' => 'avatar.png',
            ]
        )->shouldBeCalled()->willThrow(new FileTransferException('File not found'));
        $stepExecution->addWarning(
            'The avatar file was not found or is not currently available: File not found',
            [],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalled();
        $objectDetacher->detach($user)->shouldBeCalled();

        $this->process($user)->shouldReturn(
            [
                'username' => 'julia',
                'email' => 'julia@example.com',
                'avatar' => [
                    'filePath' => 'a/b/c/123avatar.png',
                    'originalFilename' => 'avatar.png',
                ],
            ]
        );
    }
}
