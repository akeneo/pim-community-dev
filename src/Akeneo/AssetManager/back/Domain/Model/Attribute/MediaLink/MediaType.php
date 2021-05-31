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

namespace Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaType
{
    public const IMAGE = 'image';
    public const PDF = 'pdf';
    public const YOUTUBE = 'youtube';
    public const VIMEO = 'vimeo';
    public const OTHER = 'other';
    public const MEDIA_TYPES = [
        self::IMAGE,
        self::PDF,
        self::YOUTUBE,
        self::VIMEO,
        self::OTHER
    ];

    private string $mediaType;

    private function __construct(string $mediaType)
    {
        Assert::true(in_array($mediaType, self::MEDIA_TYPES));
        $this->mediaType = $mediaType;
    }

    public static function fromString(string $mediaType): self
    {
        Assert::notEmpty($mediaType, 'The media type cannot be an empty string');
        Assert::oneOf($mediaType, self::MEDIA_TYPES, sprintf('Expected media types are "%s", "%s" given', implode(', ', self::MEDIA_TYPES), $mediaType));

        return new self($mediaType);
    }

    public function normalize(): string
    {
        return $this->mediaType;
    }
}
