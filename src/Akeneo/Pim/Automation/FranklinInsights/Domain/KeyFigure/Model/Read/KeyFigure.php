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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read;

final class KeyFigure
{
    public const TYPE_NUMBER = 'number';

    /** @var string */
    private $name;

    /** @var int */
    private $value;

    public function __construct(string $name, int $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return self::TYPE_NUMBER;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
