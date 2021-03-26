<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Normalization;

use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    private NormalizerInterface $userNormalizer;
    private ObjectDetacherInterface $objectDetacher;
    private FilesystemProvider $filesystemProvider;
    private FileFetcherInterface $fileFetcher;
    private StepExecution $stepExecution;

    public function __construct(
        NormalizerInterface $userNormalizer,
        ObjectDetacherInterface $objectDetacher,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher
    ) {
        $this->userNormalizer = $userNormalizer;
        $this->objectDetacher = $objectDetacher;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($user): array
    {
        Assert::isInstanceOf($user, UserInterface::class);
        $normalizedUser = $this->userNormalizer->normalize($user, 'standard');

        // Fetch avatar file into the working directory
        $avatar = $user->getAvatar();
        if (null !== $avatar) {
            $workingDirectory = \rtrim(
                $this->stepExecution->getJobExecution()->getExecutionContext()->get(JobInterface::WORKING_DIRECTORY_PARAMETER),
                DIRECTORY_SEPARATOR
            );
            $filePath = \sprintf(
                'files/%s/avatar/',
                \str_replace(DIRECTORY_SEPARATOR, '_', $user->getUsername())
            );

            try {
                $filesystem = $this->filesystemProvider->getFilesystem($avatar->getStorage());
                $this->fileFetcher->fetch(
                    $filesystem,
                    $avatar->getKey(),
                    [
                        'filePath' => \sprintf('%s/%s', $workingDirectory, $filePath),
                        'filename' => $avatar->getOriginalFilename(),
                    ]
                );
                $normalizedUser['avatar']['filePath'] = $filePath . $avatar->getOriginalFilename();
            } catch (FileTransferException $e) {
                $this->stepExecution->addWarning(
                    \sprintf('The avatar file was not found or is not currently available: %s', $e->getMessage()),
                    [],
                    new DataInvalidItem(['avatar' => $avatar->getKey()])
                );
            } catch (\LogicException $e) {
                $this->stepExecution->addWarning(
                    \sprintf('The avatar file could not be copied: %s', $e->getMessage()),
                    [],
                    new DataInvalidItem(['avatar' => $avatar->getKey()])
                );
            }
        }
        $this->objectDetacher->detach($user);

        return $normalizedUser;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
