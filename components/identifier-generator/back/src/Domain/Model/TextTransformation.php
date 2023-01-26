<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type TextTransformationNormalized 'no'|'uppercase'|'downcase'
 */
final class TextTransformation
{
    public const NO = 'no';
    public const UPPERCASE = 'uppercase';
    public const DOWNCASE = 'downcase';

    private function __construct(
        private string $value
    ) {
        Assert::oneOf($this->value, [self::NO, self::UPPERCASE, self::DOWNCASE]);
    }

    public static function fromString(string $value)
    {
        return new self($value);
    }

    public function normalize(): string
    {
        return $this->value;
    }
}
