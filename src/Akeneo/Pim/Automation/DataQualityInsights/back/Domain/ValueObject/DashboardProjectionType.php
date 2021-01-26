<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
