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

final class DashboardProjectionCode
{
    const CATALOG = 'catalog';

    /** @var string */
    private $code;

    private function __construct(string $code)
    {
        $this->code = $code;
    }

    public function __toString()
    {
        return $this->code;
    }

    public static function catalog(): self
    {
        return new self(self::CATALOG);
    }

    public static function family(FamilyCode $familyCode): self
    {
        return new self(strval($familyCode));
    }

    public static function category(CategoryCode $categoryCode): self
    {
        return new self(strval($categoryCode));
    }
}
