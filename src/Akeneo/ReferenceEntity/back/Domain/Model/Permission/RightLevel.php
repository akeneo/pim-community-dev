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

namespace Akeneo\ReferenceEntity\Domain\Model\Permission;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RightLevel
{
    public const LEVELS = ['none', 'view', 'edit'];

    /** @var string */
    public $rightLevel;

    private function __construct(string $rightLevel)
    {
        Assert::oneOf($rightLevel, self::LEVELS);

        $this->rightLevel = $rightLevel;
    }

    public static function fromString(string $rightLevel): self
    {
        return new self($rightLevel);
    }

    public function normalize(): string
    {
        return $this->rightLevel;
    }
}
