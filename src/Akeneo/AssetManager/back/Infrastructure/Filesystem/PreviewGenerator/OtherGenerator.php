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
class OtherGenerator implements PreviewGeneratorInterface
{
    public const THUMBNAIL_TYPE = 'thumbnail';
    public const THUMBNAIL_SMALL_TYPE = 'thumbnail_small';
    public const PREVIEW_TYPE = 'preview';

    public const SUPPORTED_TYPES = [
        self::THUMBNAIL_TYPE => 'am_binary_thumbnail',
        self::THUMBNAIL_SMALL_TYPE => 'am_binary_thumbnail',
        self::PREVIEW_TYPE => 'am_binary_preview'
    ];

    /** @var DefaultImageProviderInterface  */
    private $defaultImageProvider;

    public function __construct(DefaultImageProviderInterface $defaultImageProvider)
    {
        $this->defaultImageProvider = $defaultImageProvider;
    }

    public function supports(string $data, AbstractAttribute $attribute, string $type): bool
    {
        return MediaLinkAttribute::ATTRIBUTE_TYPE === $attribute->getType()
            && MediaType::OTHER === $attribute->getMediaType()->normalize()
            && array_key_exists($type, self::SUPPORTED_TYPES);
    }

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        return $this->defaultImageProvider->getImageUrl(self::DEFAULT_OTHER, self::SUPPORTED_TYPES[$type]);
    }
}
