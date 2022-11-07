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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Doctrine\DBAL\Connection;

class GetNumberOfProductsImpactedByAttributeSpellingMistakesQuery implements GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byAttributeCode(AttributeCode $attributeCode): int
    {
        $query = $this->getMainQuery() . ' AND attribute_spellcheck.attribute_code = :attributeCode';

        $stmt = $this->dbConnection->executeQuery($query, ['attributeCode' => $attributeCode]);

        return intval($stmt->fetchOne());
    }

    public function forAllAttributes(): int
    {
        $stmt = $this->dbConnection->executeQuery($this->getMainQuery());

        return intval($stmt->fetchOne());
    }

    private function getMainQuery(): string
    {
        return <<<SQL
SELECT COUNT(DISTINCT(product.uuid)) AS impacted_products
FROM pimee_dqi_attribute_spellcheck AS attribute_spellcheck
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.code = attribute_spellcheck.attribute_code
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_product AS product ON product.family_id = family_attribute.family_id
WHERE attribute_spellcheck.to_improve = 1
SQL;
    }
}
