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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeLocaleQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Doctrine\DBAL\Connection;

final class AttributeLocaleQualityRepository implements AttributeLocaleQualityRepositoryInterface
{
    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function save(AttributeCode $attributeCode, LocaleCode $localeCode, Quality $quality): void
    {
        $query = <<<SQL
INSERT INTO pimee_dqi_attribute_locale_quality (attribute_code, locale, quality)
VALUES (:attributeCode, :locale, :quality)
ON DUPLICATE KEY UPDATE quality = :quality
SQL;

        $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'locale' => $localeCode,
            'quality' => $quality,
        ]);
    }
}
