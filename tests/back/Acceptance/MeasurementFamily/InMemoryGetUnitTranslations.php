<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InMemoryGetUnitTranslations implements GetUnitTranslations
{
    private static array $unitTranslations = [];

    public function byMeasurementFamilyCodeAndLocale(string $measurementFamilyCode, string $localeCode): array
    {
        $key = static::buildKey($measurementFamilyCode, $localeCode);

        return static::$unitTranslations[$key] ?? [];
    }

    /**
     * @param string[] $unitTranslations For example ['code1' => 'translation1', 'code2' => 'translation2', ...]
     */
    public static function saveUnitTranslations(
        string $measurementFamilyCode,
        string $localeCode,
        array $unitTranslations
    ): void {
        Assert::allString($unitTranslations);
        $key = static::buildKey($measurementFamilyCode, $localeCode);

        static::$unitTranslations[$key] = $unitTranslations;
    }

    private static function buildKey(string $measurementFamilyCode, string $localeCode): string
    {
        return sprintf('%s-%s', $measurementFamilyCode, $localeCode);
    }
}
