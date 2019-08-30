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
    private const DEFAULT_IMAGE = 'pim_asset_manager.default_image.image';
    public const THUMBNAIL_TYPE = 'thumbnail';
    public const THUMBNAIL_SMALL_TYPE = 'thumbnail_small';
    public const PREVIEW_TYPE = 'preview';
    public const SUPPORTED_TYPES = [
        self::THUMBNAIL_TYPE => 'am_thumbnail',
        self::THUMBNAIL_SMALL_TYPE => 'am_thumbnail_small',
        self::PREVIEW_TYPE => 'am_preview',
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

        if (!$this->cacheManager->isStored($url, self::SUPPORTED_TYPES[$type])) {
            try {
                $binary = $this->dataManager->find(self::SUPPORTED_TYPES[$type], $url);
            } catch (NotLoadableException $e) {
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, self::SUPPORTED_TYPES[$type]);
            } catch (\LogicException $e) { //Here we catch different levels of exception to display a different default image in the future
                // Trigerred when the mime type was not the good one
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, self::SUPPORTED_TYPES[$type]);
            } catch (\Exception $e) {
                // Triggered When a general exception arrised
                return $this->defaultImageProvider->getImageUrl(self::DEFAULT_IMAGE, self::SUPPORTED_TYPES[$type]);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, self::SUPPORTED_TYPES[$type]),
                $url,
                self::SUPPORTED_TYPES[$type]
            );
        }

        return $this->cacheManager->resolve($url, self::SUPPORTED_TYPES[$type]);
    }
}
