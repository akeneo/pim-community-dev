<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class BinaryImageGenerator implements PreviewGeneratorInterface
{
    private const DEFAULT_IMAGE = 'pim_asset_manager.default_image.image';
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE => 'am_binary_thumbnail',
        PreviewGeneratorRegistry::PREVIEW_TYPE => 'am_binary_preview'
    ];

    /** @var DataManager  */
    private $dataManager;

    /** @var CacheManager  */
    private $cacheManager;

    /** @var FilterManager  */
    private $filterManager;

    /** @var DefaultImageProviderInterface  */
    private $defaultImageProvider;

    public function __construct(
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        DefaultImageProviderInterface $defaultImageProvider
    ) {
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        return ImageAttribute::ATTRIBUTE_TYPE === $attribute->getType()
            && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        $previewType = self::SUPPORTED_TYPES[$type];
        if (!$this->cacheManager->isStored($data, $previewType)) {
            try {
                $binary = $this->dataManager->find($previewType, $data);
            } catch (NotLoadableException $e) {
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, $previewType);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $previewType),
                $data,
                $previewType
            );
        }

        return $this->cacheManager->resolve($data, $previewType);
    }
}
