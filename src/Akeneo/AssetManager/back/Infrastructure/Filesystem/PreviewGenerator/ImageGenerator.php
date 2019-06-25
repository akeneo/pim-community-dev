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
class ImageGenerator implements PreviewGeneratorInterface
{
    private const DEFAULT_IMAGE = 'pim_asset_file_image';

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
            && in_array($type, PreviewGeneratorRegistry::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        $mediaLink = sprintf('%s%s%s', $attribute->getPrefix()->normalize(), $data, $attribute->getSuffix()->normalize()) ;

        if (!$this->cacheManager->isStored($mediaLink, $type)) {
            try {
                $binary = $this->dataManager->find($type, $mediaLink);
            } catch (NotLoadableException $e) {
                return $this->defaultImageProvider->getImageMediaLink(self::DEFAULT_IMAGE, $type);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $type),
                $mediaLink,
                $type
            );
        }

        return $this->cacheManager->resolve($mediaLink, $type);
    }
}
