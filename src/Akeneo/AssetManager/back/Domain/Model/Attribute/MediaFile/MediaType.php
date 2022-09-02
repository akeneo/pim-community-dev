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

namespace Akeneo\AssetManager\Domain\Model\Attribute\MediaFile;

use Webmozart\Assert\Assert;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaType
{
    public const IMAGE = 'image';
    public const PDF = 'pdf';
    public const OTHER = 'other';
    public const MEDIA_TYPES = [
        self::IMAGE,
        self::PDF,
        self::OTHER
    ];

    private function __construct(private string $mediaType)
    {
        Assert::true(in_array($mediaType, self::MEDIA_TYPES));
    }

    public static function fromString(string $mediaType): self
    {
        Assert::stringNotEmpty($mediaType, 'The media type cannot be an empty string');
        Assert::oneOf($mediaType, self::MEDIA_TYPES, sprintf('Expected media types are "%s", "%s" given', implode(', ', self::MEDIA_TYPES), $mediaType));

        return new self($mediaType);
    }

    public function normalize(): string
    {
        return $this->mediaType;
    }
}
