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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkImageGenerator implements PreviewGeneratorInterface
{
    private const DEFAULT_IMAGE = 'pim_asset_manager.default_image.image'; // Should change depending on the preview type
    public const THUMBNAIL_TYPE = 'thumbnail';
    public const THUMBNAIL_SMALL_TYPE = 'thumbnail';
    public const PREVIEW_TYPE = 'preview';
    public const SUPPORTED_TYPES = [
        self::THUMBNAIL_TYPE => 'am_url_thumbnail',
        self::PREVIEW_TYPE => 'am_url_preview',
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
        return MediaLinkAttribute::ATTRIBUTE_TYPE === $attribute->getType()
               && MediaType::IMAGE === $attribute->getMediaType()->normalize()
               && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        $url = sprintf('%s%s%s', $attribute->getPrefix()->normalize(), $data, $attribute->getSuffix()->normalize()) ;
        $previewType = self::SUPPORTED_TYPES[$type];

        if (!$this->cacheManager->isStored($url, $previewType)) {
            try {
                $binary = $this->dataManager->find($previewType, $url);
            } catch (NotLoadableException $e) {
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, $previewType);
            } catch (\LogicException $e) { //Here we catch different levels of exception to display a different default image in the future
                // Trigerred when the mime type was not the good one
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, $previewType);
            } catch (\Exception $e) {
                // Triggered When a general exception arrised
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, $previewType);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $previewType),
                $url,
                $previewType
            );
        }

        return $this->cacheManager->resolve($url, $previewType);
    }
}
