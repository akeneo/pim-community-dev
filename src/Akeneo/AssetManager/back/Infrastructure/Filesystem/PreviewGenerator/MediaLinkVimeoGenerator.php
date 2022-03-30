<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class MediaLinkVimeoGenerator extends AbstractPreviewGenerator
{
    private const VIMEO_PREVIEW_URL = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/%s';
    public const DEFAULT_IMAGE = 'pim_asset_manager.default_image.image'; // Should change depending on the preview type
    public const SUPPORTED_TYPES = [
        PreviewGeneratorRegistry::THUMBNAIL_TYPE       => 'am_url_image_thumbnail',
        PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE => 'am_url_image_thumbnail',
        PreviewGeneratorRegistry::PREVIEW_TYPE         => 'am_url_image_preview',
    ];

    public function supportsMimeType(string $mimeType): bool
    {
        return true;
    }

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        return $attribute instanceof MediaLinkAttribute
            && MediaType::VIMEO === $attribute->getMediaType()->normalize()
            && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    protected function getPreviewType(string $type): string
    {
        return self::SUPPORTED_TYPES[$type];
    }

    protected function generateUrl(string $data, AbstractAttribute $attribute): string
    {
        $url = '';
        $raw = file_get_contents(sprintf(self::VIMEO_PREVIEW_URL, $data));

        if ($raw) {
            $json = json_decode($raw, true);
            $url = $json['thumbnail_url'];
        }

        return $url;
    }

    protected function defaultImage(): string
    {
        return self::DEFAULT_IMAGE;
    }
}
