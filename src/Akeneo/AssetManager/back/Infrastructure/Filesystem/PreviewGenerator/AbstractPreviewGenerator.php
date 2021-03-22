<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPreviewGenerator implements PreviewGeneratorInterface
{
    /** The limit above which we do not try to generate a preview, in bytes */
    private const PREVIEW_SIZE_LIMIT = 60000000;

    /** @var DefaultImageProviderInterface */
    protected $defaultImageProvider;

    /** @var DataManager */
    protected $dataManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        DefaultImageProviderInterface $defaultImageProvider,
        LoggerInterface $logger
    ) {
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->defaultImageProvider = $defaultImageProvider;
        $this->logger = $logger;
    }

    abstract public function supports(string $data, AbstractAttribute $attribute, string $type): bool;

    /**
     * {@inheritDoc}
     */
    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        if (empty($data)) {
            return $this->getDefaultImageUrl($type);
        }

        if (!$this->isBase64Encoded($data)) {
            $this->logger->error(
                'The preview generator for type requires a base64 encoded input.',
                [
                    'data'      => $data,
                    'attribute' => $attribute->normalize(),
                ]
            );

            return $this->getDefaultImageUrl($type);
        }

        $data = base64_decode($data, true);
        $url = $this->generateUrl($data, $attribute);
        $filename = $this->createCacheFilename($url, $type);
        $previewType = $this->getPreviewType($type);

        try {
            $isStored = $this->cacheManager->isStored($filename, $previewType);

            if (!$isStored) {
                $binary = $this->dataManager->find($previewType, $url);

                if (self::PREVIEW_SIZE_LIMIT < strlen($binary->getContent())) {
                    throw new \LogicException('The file is too large to generate a preview');
                }

                $file = $this->filterManager->applyFilter($binary, $previewType);
                $this->cacheManager->store(
                    $file,
                    $filename,
                    $previewType
                );
            }
        } catch (RuntimeException $exception) {
            $this->logger->error(
                'Exception when trying to create a thumbnail',
                [
                    'data'        => $data,
                    'attribute'   => $attribute->normalize(),
                    'exception'   => [
                        'type'    => get_class($exception),
                        'message' => $exception->getMessage(),
                    ],
                ]
            );

            throw new CouldNotGeneratePreviewException($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error(
                'Exception when trying to create a thumbnail',
                [
                    'data'        => $data,
                    'attribute'   => $attribute->normalize(),
                    'exception'   => [
                        'type'    => get_class($exception),
                        'message' => $exception->getMessage(),
                    ],
                ]
            );

            return $this->getDefaultImageUrl($type);
        }

        return $this->cacheManager->resolve($filename, $previewType);
    }

    public function remove(string $data, AbstractAttribute $attribute, string $type)
    {
        if (empty($data)) {
            return;
        }

        if (!$this->isBase64Encoded($data)) {
            $this->logger->error(
                'The preview generator for type requires a base64 encoded input.',
                [
                    'data'      => $data,
                    'attribute' => $attribute->normalize(),
                ]
            );

            return;
        }

        $data = base64_decode($data, true);
        $url = $this->generateUrl($data, $attribute);
        $filename = $this->createCacheFilename($url, $type);
        $previewType = $this->getPreviewType($type);

        $this->cacheManager->remove($filename, $previewType);
    }

    /**
     * Check whether the given string is correctly encoded in base64
     */
    private function isBase64Encoded(string $data): bool
    {
        $decoded = base64_decode($data, true);

        return false !== $decoded && base64_encode($decoded) === $data;
    }

    /**
     * Create an unique filename for the given url.
     * The file extension is calculated from the preview type configuration.
     *
     * @param string $url
     * @param string $type
     *
     * @return string
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
            $hashedFilename[3]
        );

        return sprintf('%s%s%s', $path, $hashedFilename, $fileExtension);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getDefaultImageUrl(string $type): string
    {
        $previewType = $this->getPreviewType($type);
        $defaultImage = $this->defaultImage();

        return $this->defaultImageProvider->getImageUrl($defaultImage, $previewType);
    }

    abstract protected function getPreviewType(string $type): string;

    abstract protected function generateUrl(string $data, AbstractAttribute $attribute): string;

    abstract protected function defaultImage(): string;
}
