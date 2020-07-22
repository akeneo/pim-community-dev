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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure;

final class SpellCheckResult
{
    /** @var bool */
    private $isToImprove;

    public function __construct(bool $hasMistake)
    {
        $this->isToImprove = $hasMistake;
    }

    public function isToImprove(): bool
    {
        return $this->isToImprove;
    }

    public static function good(): self
    {
        return new self(false);
    }

    public static function toImprove(): self
    {
        return new self(true);
    }
}
