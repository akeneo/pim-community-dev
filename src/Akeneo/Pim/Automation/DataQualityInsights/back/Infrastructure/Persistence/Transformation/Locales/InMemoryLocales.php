<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryLocales implements LocalesInterface
{
    private array $localesIdsByCodes;
    private array $localesCodesByIds;

    /**
     * @param array<string, int> $localesIdsByCodes
     */
    public function __construct(array $localesIdsByCodes)
    {
        $this->localesIdsByCodes = $localesIdsByCodes;
        $this->localesCodesByIds = array_flip($localesIdsByCodes);
    }

    public function getIdByCode(string $code): ?int
    {
        return $this->localesIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        return $this->localesCodesByIds[$id] ?? null;
    }
}
