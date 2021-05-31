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

namespace Akeneo\AssetManager\Domain\Model\Permission;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RightLevel
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    public const LEVELS = [self::VIEW, self::EDIT];

    private string $rightLevel;

    private function __construct(string $rightLevel)
    {
        Assert::oneOf($rightLevel, self::LEVELS);

        $this->rightLevel = $rightLevel;
    }

    public static function fromString(string $rightLevel): self
    {
        return new self($rightLevel);
    }

    public static function edit(): self
    {
        return new self(self::EDIT);
    }

    public static function view(): self
    {
        return new self(self::VIEW);
    }

    public function normalize(): string
    {
        return $this->rightLevel;
    }

    public function equals(RightLevel $otherRightLevel): bool
    {
        return $this->rightLevel === $otherRightLevel->rightLevel;
    }
}
