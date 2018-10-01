<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\Record;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCode
{
    /** @var string */
    private $code;

    private function __construct(string $code)
    {
        Assert::stringNotEmpty($code, 'Record code cannot be empty');
        Assert::maxLength(
            $code,
            255,
            sprintf('Record code cannot be longer than 255 characters, %d string long given', strlen($code))
        );
        Assert::regex(
            $code,
            '/^[a-zA-Z0-9_]+$/',
            sprintf('Record code may contain only letters, numbers and underscores. "%s" given', $code)
        );

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function equals(RecordCode $code): bool
    {
        return $this->code === $code->code;
    }

    public function normalize(): string
    {
        return $this->code;
    }
}
