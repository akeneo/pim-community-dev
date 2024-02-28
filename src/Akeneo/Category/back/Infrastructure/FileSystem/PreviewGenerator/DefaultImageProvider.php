<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem\PreviewGenerator;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultImageProvider implements DefaultImageProviderInterface
{
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE => 'am_binary_image_thumbnail_category',
        PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE => 'am_binary_image_thumbnail_small_category',
        PreviewGeneratorRegistry::PREVIEW_TYPE => 'am_binary_image_preview_category',
    ];
    protected ?array $defaultImages = null;

    public function __construct(protected FilterManager $filterManager, protected CacheManager $cacheManager, array $defaultImages)
    {
        $resolver = new OptionsResolver();
        $this->configureDefaultImagesOptions($resolver);
        foreach ($defaultImages as $fileType => $defaultImage) {
            $this->defaultImages[$fileType] = $resolver->resolve($defaultImage);
        }
    }

    public function getImageUrl(string $fileKey, string $filter): string
    {
        $filter = self::SUPPORTED_TYPES[$filter] ?? $filter;

        if (!$this->cacheManager->isStored($fileKey, $filter)) {
            $binary = $this->getImageBinary($fileKey);
            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $fileKey,
                $filter,
            );
        }

        return $this->cacheManager->resolve($fileKey, $filter);
    }

    public function getImageBinary(string $fileType): Binary
    {
        if (isset($this->defaultImages[$fileType])) {
            $image = $this->defaultImages[$fileType];

            return new Binary(file_get_contents($image['path']), $image['mime_type'], $image['extension']);
        }

        throw new \InvalidArgumentException(sprintf('No default image is defined for file type "%s"', $fileType));
    }

    /**
     * Ensure $defaultImages parameter validity.
     */
    private function configureDefaultImagesOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['path', 'mime_type', 'extension']);
    }
}
