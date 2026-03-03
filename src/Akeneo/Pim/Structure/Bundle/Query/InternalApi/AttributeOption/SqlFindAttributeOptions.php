<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlFindAttributeOptions implements FindAttributeOptions
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UserContext $userContext,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function search(
        string $attributeCode,
        string $search = '',
        int $page = 1,
        int $limit = 20,
        ?array $includeCodes = null,
    ): array {
        $uiLocaleCode = $this->userContext->getUiLocaleCode();

        $sql = <<<SQL
            SELECT attribute_option.code AS option_code,
                translation.value AS option_label  
            FROM pim_catalog_attribute_option attribute_option
                LEFT JOIN pim_catalog_attribute_option_value translation 
                    ON attribute_option.id = translation.option_id
                    AND translation.locale_code = :userLocaleCode
                INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id=attribute_option.attribute_id 
            WHERE pim_catalog_attribute.code=:attributeCode
                AND (
                    attribute_option.code LIKE :search
                    OR translation.value LIKE :search
                )
                {includeCodesQuery}
            GROUP BY attribute_option.code, attribute_option.sort_order, translation.value
            ORDER BY attribute_option.sort_order, attribute_option.code
        SQL;

        $parameters = [
            'attributeCode' => $attributeCode,
            'search' => '%' . $search . '%',
            'userLocaleCode' => $uiLocaleCode,
        ];

        $types = [];

        if ($limit !== -1) {
            $sql .= ' LIMIT :offset, :limit';
            $parameters['limit'] = $limit;
            $parameters['offset'] = ($page - 1) * $limit;
            $types['limit'] = \PDO::PARAM_INT;
            $types['offset'] = \PDO::PARAM_INT;
        }

        if (null !== $includeCodes) {
            $sql = \strtr($sql, ['{includeCodesQuery}' => 'AND attribute_option.code IN (:includeCodes)']);
            $parameters['includeCodes'] = $includeCodes;
            $types['includeCodes'] = Connection::PARAM_STR_ARRAY;
        } else {
            $sql = \strtr($sql, ['{includeCodesQuery}' => '']);
        }

        $results = $this->connection->fetchAllAssociative(
            $sql,
            $parameters,
            $types
        );

        return array_map(fn ($line) => [
            'code' => $line['option_code'],
            'labels' => [$uiLocaleCode => $line['option_label'] ?? \sprintf('[%s]', $line['option_code'])],
        ], $results);
    }
}
