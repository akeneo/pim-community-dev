<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
