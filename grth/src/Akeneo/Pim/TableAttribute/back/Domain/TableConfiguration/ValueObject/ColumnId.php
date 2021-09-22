<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Webmozart\Assert\Assert;

final class ColumnId
{
    private const REGEX_VALIDATION = '/^(?P<columnCode>[\w]*)_(?P<uniqueId>[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12})$/';
    private string $id;

    private function __construct(string $id)
    {
        Assert::regex($id, self::REGEX_VALIDATION, sprintf('The "%s" id is not valid', $id));
        $this->id = $id;
    }

    public static function fromString(string $id): self
    {
        Assert::stringNotEmpty($id);

        return new self($id);
    }

    public static function createFromColumnCode(ColumnCode $code, string $unique): self
    {
        Assert::stringNotEmpty($unique);

        return new self(sprintf('%s_%s', $code->asString(), $unique));
    }

    public function asString(): string
    {
        return $this->id;
    }

    public function extractColumnCode(): ColumnCode
    {
        $res = preg_match(self::REGEX_VALIDATION, $this->id, $matches);
        if (1 !== $res || count($matches) < 2) {
            throw new \LogicException('The id is malformed.');
        }

        return ColumnCode::fromString($matches[1]);
    }

    public function equals(ColumnId $otherColumnId): bool
    {
        return \strtolower($this->asString()) === \strtolower($otherColumnId->asString());
    }
}
