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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class DashboardProjectionType
{
    const CATALOG = 'catalog';
    const CATEGORY = 'category';
    const FAMILY = 'family';

    /** @var string */
    private $type;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->type;
    }

    public static function catalog(): self
    {
        return new self(self::CATALOG);
    }

    public static function family(): self
    {
        return new self(self::FAMILY);
    }

    public static function category(): self
    {
        return new self(self::CATEGORY);
    }
}
