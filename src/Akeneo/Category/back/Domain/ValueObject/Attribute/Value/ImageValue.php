<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ImageValue
{
    /**
     * @param array{
     *     size: int,
     *     extension: string,
     *     file_path: string,
     *     mime_type: string,
     *     original_filename: string,
     * } $value
     */
    public function __construct(public readonly array $value)
    {
        Assert::allString($value);
        Assert::allStringNotEmpty($this->$value);
    }

    /**
     * @param array{
     *     size: int,
     *     extension: string,
     *     file_path: string,
     *     mime_type: string,
     *     original_filename: string,
     * } $value
     */
    public static function fromArray(array $value): self
    {
        return new self($value);
    }
}
