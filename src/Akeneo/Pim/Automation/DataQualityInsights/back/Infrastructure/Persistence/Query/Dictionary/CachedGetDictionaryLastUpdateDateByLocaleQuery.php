<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetDictionaryLastUpdateDateByLocaleQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

final class CachedGetDictionaryLastUpdateDateByLocaleQuery implements GetDictionaryLastUpdateDateByLocaleQueryInterface
{
    /**
     * @var array<string, ?\DateTimeImmutable>
     */
    private array $lastUpdateDatesByLocale = [];

    private bool $loaded = false;

    public function __construct(
        private Connection $dbConnection
    ) {
    }

    public function execute(LocaleCode $localeCode): ?\DateTimeImmutable
    {
        if (false === $this->loaded) {
            $this->loadLastUpdateDatesByLocale();
        }

        return $this->lastUpdateDatesByLocale[(string) $localeCode] ?? null;
    }

    private function loadLastUpdateDatesByLocale(): void
    {
        $this->lastUpdateDatesByLocale = [];

        $query = <<<SQL
SELECT locale_code, MAX(updated_at) AS last_update_date
FROM pimee_data_quality_insights_text_checker_dictionary
GROUP BY locale_code;
SQL;

        $stmt = $this->dbConnection->executeQuery($query);

        while ($row = $stmt->fetchAssociative()) {
            $this->lastUpdateDatesByLocale[$row['locale_code']] = $row['last_update_date']
                ? Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['last_update_date'], $this->dbConnection->getDatabasePlatform())
                : null;
        }

        $this->loaded = true;
    }
}
