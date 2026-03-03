<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupActivationRepository implements AttributeGroupActivationRepositoryInterface
{
    /** @var Connection */
    protected $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function save(AttributeGroupActivation $attributeGroupActivation): void
    {
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_attribute_group_activation (attribute_group_code, activated, updated_at) 
VALUES (:attributeGroupCode, :activated, NOW())
ON DUPLICATE KEY UPDATE activated = :activated, updated_at = NOW();
SQL;

        $this->dbConnection->executeQuery(
            $query,
            [
                'attributeGroupCode' => $attributeGroupActivation->getAttributeGroupCode(),
                'activated' => $attributeGroupActivation->isActivated(),
            ],
            [
                'attributeGroupCode' => \PDO::PARAM_STR,
                'activated' => \PDO::PARAM_BOOL,
            ]
        );
    }

    public function remove(AttributeGroupCode $attributeGroupCode): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_attribute_group_activation WHERE attribute_group_code = :attributeGroupCode;
SQL;

        $this->dbConnection->executeQuery($query, ['attributeGroupCode' => $attributeGroupCode]);
    }

    public function getForAttributeGroupCode(AttributeGroupCode $attributeGroupCode): ?AttributeGroupActivation
    {
        $query = <<<SQL
SELECT attribute_group_code, activated
FROM pim_data_quality_insights_attribute_group_activation
WHERE attribute_group_code = :attribute_group_code
SQL;

        $row = $this->dbConnection
            ->executeQuery($query, ['attribute_group_code' => (string) $attributeGroupCode])
            ->fetchAssociative();

        if (false === $row) {
            return null;
        }

        return new AttributeGroupActivation($attributeGroupCode, (bool) $row['activated']);
    }
}
