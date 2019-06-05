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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute\Url;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PreviewType
{
    private const IMAGE = 'image';
    private const OTHER = 'other';
    private const PREVIEW_TYPES = [
        self::IMAGE,
        self::OTHER
    ];

    /** @var string */
    private $previewType;

    private function __construct(string $previewType)
    {
        $this->previewType = $previewType;
    }

    public static function fromString(string $previewType): self
    {
        Assert::notEmpty($previewType, 'The preview type cannot be an empty string');
        Assert::oneOf($previewType, self::PREVIEW_TYPES, sprintf('Expected preview types are "%s", "%s" given', implode(', ', self::PREVIEW_TYPES), $previewType));

        return new self($previewType);
    }

    public function normalize(): string
    {
        return $this->previewType;
    }
}
