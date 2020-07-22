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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\IsAttributeLocalizableQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Doctrine\DBAL\Connection;

final class IsAttributeLocalizableQuery implements IsAttributeLocalizableQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byCode(AttributeCode $attributeCode): bool
    {
        $query = <<<SQL
SELECT is_localizable FROM pim_catalog_attribute WHERE code = :attributeCode
SQL;

        $attributeData = $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeCode
        ])->fetch(\PDO::FETCH_ASSOC);

        return (bool) ($attributeData['is_localizable'] ?? false);
    }
}
