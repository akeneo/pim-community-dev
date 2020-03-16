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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class AttributeType
{
    const TEXT = 'text';
    const TEXTAREA = 'textarea';

    /** @var string */
    private $type;

    private function __construct(string $code)
    {
        $this->type = $code;
    }

    public function __toString()
    {
        return $this->type;
    }

    public function equals(AttributeType $attributeType): bool
    {
        return $this->type === strval($attributeType);
    }

    public static function text(): self
    {
        return new self(self::TEXT);
    }

    public static function textarea(): self
    {
        return new self(self::TEXTAREA);
    }
}
