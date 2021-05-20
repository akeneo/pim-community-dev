<?php

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class DefaultImageProvider implements DefaultImageProviderInterface
{
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE        => 'am_binary_image_thumbnail',
        PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE  => 'am_binary_image_thumbnail',
        PreviewGeneratorRegistry::PREVIEW_TYPE          => 'am_binary_image_preview'
    ];

    protected FilterManager $filterManager;

    protected CacheManager $cacheManager;

    protected ?array $defaultImages = null;

    /**
     * @param FilterManager $filterManager
     * @param CacheManager  $cacheManager
     * @param array         $defaultImages
     */
    public function __construct(FilterManager $filterManager, CacheManager $cacheManager, array $defaultImages)
    {
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;

        $resolver = new OptionsResolver();
        $this->configureDefaultImagesOptions($resolver);
        foreach ($defaultImages as $fileType => $defaultImage) {
            $this->defaultImages[$fileType] = $resolver->resolve($defaultImage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl($fileKey, $filter)
    {
        $filter = isset(self::SUPPORTED_TYPES[$filter]) ? self::SUPPORTED_TYPES[$filter] : $filter;

        if (!$this->cacheManager->isStored($fileKey, $filter)) {
            $binary = $this->getImageBinary($fileKey);
            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $fileKey,
                $filter
            );
        }
        return $this->cacheManager->resolve($fileKey, $filter);
    }

    /**
     * Return a Binary instance that embed the image corresponding to the specified file type
     *
     * @param string $fileType
     *
     * @throws \InvalidArgumentException
     *
     * @return Binary
     */
    public function getImageBinary($fileType)
    {
        if (isset($this->defaultImages[$fileType])) {
            $image = $this->defaultImages[$fileType];

            return new Binary(file_get_contents($image['path']), $image['mime_type'], $image['extension']);
        }

        throw new \InvalidArgumentException(sprintf('No default image is defined for file type "%s"', $fileType));
    }

    /**
     * Ensure $defaultImages parameter validity
     *
     * @param OptionsResolver $resolver
     */
    private function configureDefaultImagesOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['path', 'mime_type', 'extension']);
    }
}
