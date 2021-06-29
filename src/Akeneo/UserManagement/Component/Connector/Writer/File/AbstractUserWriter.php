<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractUserWriter extends AbstractFileWriter implements
    ItemWriterInterface,
    InitializableInterface,
    FlushableInterface
{
    private ArrayConverterInterface $arrayConverter;
    private BufferFactory $bufferFactory;
    private FlatItemBufferFlusher $flusher;
    private ?FlatItemBuffer $flatRowBuffer = null;
    private FileInfoRepositoryInterface $fileInfoRepository;
    private FilesystemProvider $filesystemProvider;
    private FileExporterPathGeneratorInterface $pathGenerator;

    public function __construct(
        ArrayConverterInterface $arrayConverter,
        BufferFactory $bufferFactory,
        FlatItemBufferFlusher $flusher,
        FileInfoRepositoryInterface $fileInfoRepository,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $pathGenerator
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->bufferFactory = $bufferFactory;
        $this->flusher = $flusher;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->pathGenerator = $pathGenerator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    final public function initialize(): void
    {
        if (null === $this->flatRowBuffer) {
            $this->flatRowBuffer = $this->bufferFactory->create();
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function write(array $items): void
    {
        $exportDirectory = dirname($this->getPath());
        $this->localFs->mkdir($exportDirectory);

        $flatItems = [];
        foreach ($items as $item) {
            $item = $this->resolveAvatarPath($item);
            $flatItems[] = $this->arrayConverter->convert($item, []);
        }

        $this->flatRowBuffer->write(
            $flatItems,
            ['withHeader' => $this->stepExecution->getJobParameters()->get('withHeader')]
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function flush(): void
    {
        $this->flusher->setStepExecution($this->stepExecution);
        $parameters = $this->stepExecution->getJobParameters();
        $writtenFiles = $this->flusher->flush(
            $this->flatRowBuffer,
            $this->getWriterConfiguration(),
            $this->getPath(),
            ($parameters->has('linesPerFile') ? $parameters->get('linesPerFile') : -1)
        );

        foreach ($writtenFiles as $writtenFile) {
            $this->writtenFiles[] = WrittenFileInfo::fromLocalFile($writtenFile, \basename($writtenFile));
        }
    }

    private function resolveAvatarPath(array $item): array
    {
        $avatarKey = $item['avatar']['filePath'] ?? null;
        if (null === $avatarKey) {
            return $item;
        }

        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($avatarKey);
        if ($fileInfo instanceof FileInfoInterface) {
            $outputPath = $this->pathGenerator->generate(
                ['scope' => null, 'locale' => null],
                ['code' => 'avatar', 'identifier' => $item['username']],
            );
            $outputAvatarPath = $outputPath . $fileInfo->getOriginalFilename();

            if (!$this->filesystemProvider->getFilesystem($fileInfo->getStorage())->has($fileInfo->getKey())) {
                $this->stepExecution->addWarning(
                    'The media has not been found or is not currently available',
                    [],
                    new DataInvalidItem(
                        [
                            'from' => $fileInfo->getKey(),
                            'to' => [
                                'filePath' => \dirname($outputAvatarPath),
                                'filename' => \basename($outputAvatarPath),
                            ],
                            'storage' => $fileInfo->getStorage(),
                        ]
                    )
                );
            } else {
                $item['avatar']['filePath'] = $outputAvatarPath;
                $this->writtenFiles[] = WrittenFileInfo::fromFileStorage(
                    $fileInfo->getKey(),
                    $fileInfo->getStorage(),
                    $outputAvatarPath
                );
            }
        }

        return $item;
    }

    abstract protected function getWriterConfiguration(): array;
}
