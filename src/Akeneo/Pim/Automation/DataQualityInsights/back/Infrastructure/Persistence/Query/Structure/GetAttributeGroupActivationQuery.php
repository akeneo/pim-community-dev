<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAttributeGroupActivationQuery implements GetAttributeGroupActivationQueryInterface
{
    /** @var Connection */
    protected $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function byCode(AttributeGroupCode $attributeGroupCode): ?AttributeGroupActivation
    {
        $query = <<<SQL
SELECT activated FROM pim_data_quality_insights_attribute_group_activation
WHERE attribute_group_code = :attributeGroupCode;
SQL;

        $result = $this->dbConnection->executeQuery($query, ['attributeGroupCode' => $attributeGroupCode])
            ->fetchOne();

        return false !== $result ? new AttributeGroupActivation($attributeGroupCode, (bool) $result) : null;
    }
}
