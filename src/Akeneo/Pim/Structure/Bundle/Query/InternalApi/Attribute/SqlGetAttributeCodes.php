<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeCodes;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAttributeCodes implements GetAttributeCodes
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function forAttributeTypes(array $attributeTypes): array
    {
        Assert::allString($attributeTypes);

        if ([] === $attributeTypes) {
            return [];
        }

        return $this->connection->executeQuery(
            'SELECT code FROM pim_catalog_attribute WHERE attribute_type IN (:attributeTypes)',
            ['attributeTypes' => $attributeTypes],
            ['attributeTypes' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();
    }
}
