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
    /** @var null|array */
    private $localeIdsByCodes;

    /** @var null|array */
    private $localeCodesByIds;

    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getIdByCode(string $code): ?int
    {
        if (null === $this->localeIdsByCodes) {
            $this->loadLocales();
        }

        return $this->localeIdsByCodes[$code] ?? null;
    }

    public function getCodeById(int $id): ?string
    {
        if (null === $this->localeCodesByIds) {
            $this->loadLocales();
        }

        return $this->localeCodesByIds[$id] ?? null;
    }

    private function loadLocales(): void
    {
        $this->localeIdsByCodes = [];
        $this->localeCodesByIds = [];

        $locales = $this->dbConnection->executeQuery(
            'SELECT JSON_OBJECTAGG(id, code) FROM pim_catalog_locale WHERE is_activated = 1;'
        )->fetchColumn();

        if ($locales) {
            $this->localeCodesByIds = json_decode($locales, true);
            $this->localeIdsByCodes = array_flip($this->localeCodesByIds);
        }
    }
}
