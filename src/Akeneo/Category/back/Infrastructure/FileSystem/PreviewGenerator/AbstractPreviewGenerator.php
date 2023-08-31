<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPreviewGenerator implements PreviewGeneratorInterface
{
    /** The limit above which we do not try to generate a preview, in bytes */
    private const PREVIEW_SIZE_LIMIT = 60_000_000;

    /**
     * @param ?string[] $supportedMimeTypes
     */
    public function __construct(
        protected DataManager $dataManager,
        protected CacheManager $cacheManager,
        protected FilterManager $filterManager,
        protected DefaultImageProviderInterface $defaultImageProvider,
        protected LoggerInterface $logger,
        protected ?array $supportedMimeTypes = [],
    ) {
    }

    abstract public function supports(string $data, Attribute $attribute, string $type): bool;

    public function supportsMimeType(string $mimeType): bool
    {
        return \in_array(\strtolower($mimeType), $this->supportedMimeTypes);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $data, Attribute $attribute, string $type): string
    {
        if (empty($data)) {
            return $this->getDefaultImageUrl($type);
        }

        if (!$this->isBase64Encoded($data)) {
            $this->logger->notice(
                'The preview generator for type requires a base64 encoded input.',
                [
                    'data' => $data,
                    'attribute' => $attribute->normalize(),
                ],
            );

            return $this->getDefaultImageUrl($type);
        }

        $data = base64_decode($data, true);
        $filename = $this->createCacheFilename($data, $type);
        $previewType = $this->getPreviewType($type);

        try {
            $isStored = $this->cacheManager->isStored($filename, $previewType);

            if (!$isStored) {
                $binary = $this->dataManager->find($previewType, $data);
                $content = $binary->getContent();

                if (null === $content) {
                    throw new CouldNotGeneratePreviewException('The file content is empty');
                }

                if (self::PREVIEW_SIZE_LIMIT < strlen($content)) {
                    throw new CouldNotGeneratePreviewException('The file is too large to generate a preview');
                }

                $mimeType = $binary->getMimeType();

                if (!$this->supportsMimeType($mimeType)) {
                    throw new CouldNotGeneratePreviewException(sprintf('The mime type "%s" is not supported for the preview type "%s"', $mimeType, $previewType));
                }

                $file = $this->filterManager->applyFilter($binary, $previewType);
                $this->cacheManager->store(
                    $file,
                    $filename,
                    $previewType,
                );
            }
        } catch (RuntimeException $exception) {
            $this->logger->notice(
                'Exception when trying to create a thumbnail',
                [
                    'data' => $data,
                    'attribute' => $attribute->normalize(),
                    'exception' => [
                        'type' => $exception::class,
                        'message' => $exception->getMessage(),
                    ],
                ],
            );

            throw new CouldNotGeneratePreviewException($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->notice(
                'Exception when trying to create a thumbnail',
                [
                    'data' => $data,
                    'attribute' => $attribute->normalize(),
                    'exception' => [
                        'type' => $exception::class,
                        'message' => $exception->getMessage(),
                    ],
                ],
            );

            return $this->getDefaultImageUrl($type);
        }

        return $this->cacheManager->resolve($filename, $previewType);
    }

    public function remove(string $data, string $type): void
    {
        if (empty($data)) {
            return;
        }

        if (!$this->isBase64Encoded($data)) {
            $this->logger->notice(
                'The preview generator for type requires a base64 encoded input.',
                [
                    'data' => $data,
                ],
            );

            return;
        }

        $data = base64_decode($data, true);
        $filename = $this->createCacheFilename($data, $type);
        $previewType = $this->getPreviewType($type);

        try {
            $this->cacheManager->remove($filename, $previewType);
        } catch (\Exception $exception) {
            $this->logger->notice(
                'Exception when trying to remove a thumbnail',
                [
                    'data' => $data,
                    'exception' => [
                        'type' => $exception::class,
                        'message' => $exception->getMessage(),
                    ],
                ],
            );
        }
    }

    /**
     * Check whether the given string is correctly encoded in base64.
     */
    private function isBase64Encoded(string $data): bool
    {
        $decoded = base64_decode($data, true);

        return false !== $decoded && base64_encode($decoded) === $data;
    }

    /**
     * Create an unique filename for the given url.
     * The file extension is calculated from the preview type configuration.
     */
    private function createCacheFilename(string $url, string $type): string
    {
        $previewFilterId = $this->getPreviewType($type);
        $previewFilterConfiguration = $this->filterManager->getFilterConfiguration()->get($previewFilterId);
        $previewFormat = $previewFilterConfiguration['format'] ?? \pathinfo($url, PATHINFO_EXTENSION) ?? null;

        $hashedFilename = sha1($url);
        $fileExtension = $previewFormat !== null ? sprintf('.%s', $previewFormat) : '';
        $path = sprintf(
            '%s/%s/%s/%s/',
            $hashedFilename[0],
            $hashedFilename[1],
            $hashedFilename[2],
            $hashedFilename[3],
        );

        return sprintf('%s%s%s', $path, $hashedFilename, $fileExtension);
    }

    private function getDefaultImageUrl(string $type): string
    {
        $previewType = $this->getPreviewType($type);
        $defaultImage = $this->defaultImage();

        return $this->defaultImageProvider->getImageUrl($defaultImage, $previewType);
    }

    abstract protected function getPreviewType(string $type): string;

    abstract protected function defaultImage(): string;
}
