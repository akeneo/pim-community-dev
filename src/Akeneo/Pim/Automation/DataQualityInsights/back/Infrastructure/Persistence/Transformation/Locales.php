<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Locales
{
    private array $localeIdsByCodes;

    private array $localeCodesByIds;

    private bool $localesLoaded;

    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->localeIdsByCodes = [];
        $this->localeCodesByIds = [];
        $this->localesLoaded = false;
    }

    public function getIdByCode(string $code): ?int
    {
        if (false === $this->localesLoaded) {
            $this->loadLocales();
        }

        return $this->localeIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        if (false === $this->localesLoaded) {
            $this->loadLocales();
        }

        return $this->localeCodesByIds[$id] ?? null;
    }

    private function loadLocales(): void
    {
        $locales = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_locale WHERE is_activated = 1;'
        )->fetchColumn();

        if ($locales) {
            $this->localeCodesByIds = json_decode($locales, true);
            $this->localeIdsByCodes = array_flip($this->localeCodesByIds);
        }

        $this->localesLoaded = true;
    }
}
