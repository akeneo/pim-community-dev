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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationCode
{
    public const REGEX = '/^\w+$/';

    /** @var string */
    private $code;

    private function __construct(string $code)
    {
        Assert::stringNotEmpty($code, 'Transformation code cannot be empty');
        Assert::maxLength(
            $code,
            255,
            sprintf('Transformation code cannot be longer than 255 characters, %d string long given', strlen($code))
        );
        Assert::regex(
            $code,
            self::REGEX,
            sprintf('Transformation code may contain only letters, numbers and underscores. "%s" given', $code)
        );

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function toString(): string
    {
        return $this->code;
    }

    public function equals(TransformationCode $otherCode): bool
    {
        return $this->code === $otherCode->toString();
    }

    public function normalize(): string
    {
        return $this->code;
    }
}
