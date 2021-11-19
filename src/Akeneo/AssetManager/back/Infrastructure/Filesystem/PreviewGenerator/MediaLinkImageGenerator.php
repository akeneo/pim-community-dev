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

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkImageGenerator extends AbstractPreviewGenerator
{
    private const DEFAULT_IMAGE = 'pim_asset_manager.default_image.image';
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE       => 'am_url_image_thumbnail',
        PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE => 'am_url_image_thumbnail_small',
        PreviewGeneratorRegistry::PREVIEW_TYPE         => 'am_url_image_preview',
    ];

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        return $attribute instanceof MediaLinkAttribute
            && MediaType::IMAGE === $attribute->getMediaType()->normalize()
            && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    protected function generateUrl(string $data, AbstractAttribute $attribute): string
    {
        if (!$attribute instanceof MediaLinkAttribute) {
            throw new \InvalidArgumentException('The attribute must be a MediaLinkAttribute');
        }

        return sprintf('%s%s%s', $attribute->getPrefix()->normalize(), $data, $attribute->getSuffix()->normalize());
    }

    protected function getPreviewType(string $type): string
    {
        return self::SUPPORTED_TYPES[$type];
    }

    protected function defaultImage(): string
    {
        return self::DEFAULT_IMAGE;
    }
}
