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

final class Quality
{
    public const GOOD = 'good';
    public const TO_IMPROVE = 'to_improve';
    public const PROCESSING = 'in_progress';
    public const NOT_APPLICABLE = 'n_a';

    public const FILTERS = [
        self::GOOD,
        self::TO_IMPROVE,
    ];

    /** @var string */
    private $quality;

    private function __construct(string $quality)
    {
        $this->quality = $quality;
    }

    public static function good(): self
    {
        return new self(self::GOOD);
    }

    public static function toImprove(): self
    {
        return new self(self::TO_IMPROVE);
    }

    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    public static function notApplicable(): self
    {
        return new self(self::NOT_APPLICABLE);
    }

    public function __toString()
    {
        return $this->quality;
    }
}
