<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;


use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\DBAL\Connection;

class AttributeRepository
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findOneByIdentifier(string $code): ?AttributeInterface
    {
        $sql = <<<SQL
          SELECT
            * 
          FROM
            pim_catalog_attribute
          WHERE 
            code = :code
SQL;

        $row = $this->connection->executeQuery($sql, ['code' => $code])->fetch();
        if (empty($row)) {
            return null;
        }

        //TODO: populate evrything to have the same conditions
        $attribute = new Attribute();
        $attribute->setAllowedExtensions($row['allowed_extensions']);
        $attribute->setAttributeType($row['attribute_type']);
        $attribute->setBackendType($row['backend_type']);
        //$attribute->setCode($row['code']);
        //$attribute->setCreated($row['type']);
        //$attribute->setDateMax($row['type']);
        //$attribute->setDateMin($row['type']);
        //$attribute->setDecimalsAllowed($row['type']);
        //$attribute->setDefaultMetricUnit($row['type']);
        //$attribute->setEntityType($row['type']);
        //$attribute->setId($row['type']);
        //$attribute->setLabel($row['type']);
        //$attribute->setLocalizable($row['type']);
        //$attribute->setScopable($row['type']);
        //$attribute->setMaxCharacters($row['type']);
        //$attribute->setMaxFileSize($row['type']);
        //$attribute->setMinimumInputLength($row['type']);
        //$attribute->setNegativeAllowed($row['type']);
        //$attribute->setNumberMax($row['type']);
        //$attribute->setNumberMin($row['type']);
        //$attribute->setParameters($row['type']);
        //$attribute->setProperties($row['type']);
        //$attribute->setWysiwygEnabled($row['type']);
        //$attribute->setValidationRule($row['type']);
        //$attribute->setValidationRegexp($row['type']);
        //$attribute->setUseableAsGridFilter($row['type']);
        //$attribute->setUnique($row['type']);
        //$attribute->setSortOrder($row['type']);

        //$attribute->set($row['type']);

        return $attribute;
    }

}
