<?php

namespace Akeneo\Pim\Enrichment\Bundle\StructureVersion\Provider;

use Akeneo\Platform\Bundle\UIBundle\Provider\StructureVersion\StructureVersionProviderInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Structure version provider
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureVersion implements StructureVersionProviderInterface
{
    /** @var array */
    protected $resourceNames = [];

    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function getStructureVersion()
    {
        $sql = <<<'SQL'
SELECT last_update
FROM akeneo_structure_version_last_update
WHERE resource_name IN (:resource_names)
ORDER BY last_update DESC
LIMIT 1;
SQL;

        $connection = $this->doctrine->getConnection();
        $stmt = $connection->executeQuery(
            $sql,
            ['resource_names' => $this->resourceNames],
            ['resource_names' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        $loggedAt = $stmt->fetch(\PDO::FETCH_ASSOC)['last_update'];

        if (null === $loggedAt) {
            return 0;
        }

        return $connection->convertToPHPValue($loggedAt, 'datetime')->getTimestamp();
    }

    /**
     * Add a resource name to the structure
     *
     * @param string $resourceName
     */
    public function addResource($resourceName)
    {
        if (!in_array($resourceName, $this->resourceNames)) {
            $this->resourceNames[] = $resourceName;
        }
    }
}
