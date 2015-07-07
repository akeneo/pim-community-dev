<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\VersioningBundle\Doctrine\AbstractPendingMassPersister;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Model\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Interface for service to massively insert pending versions.
 * Useful for massive imports of products.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingMassPersister extends AbstractPendingMassPersister
{
    /** @var Connection */
    protected $connection;

    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $versionTable;

    /** @var array */
    protected $versionColumns;

    /** @var ClassMetadata */
    protected $versionMetadata;

    /** @var TableNameBuilder */
    protected $tableNameBuilder;

    /**
     * @param VersionBuilder      $versionBuilder
     * @param VersionManager      $versionManager
     * @param VersionContext      $versionContext
     * @param NormalizerInterface $normalizer
     * @param string              $versionClass
     * @param Connection          $connection
     * @param EntityManager       $entityManager
     * @param TableNameBuilder    $tableNameBuilder
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        VersionContext $versionContext,
        NormalizerInterface $normalizer,
        $versionClass,
        Connection $connection,
        EntityManager $entityManager,
        TableNameBuilder $tableNameBuilder
    ) {
        parent::__construct($versionBuilder, $versionManager, $normalizer, $versionContext, $versionClass);
        $this->connection       = $connection;
        $this->entityManager    = $entityManager;
        $this->tableNameBuilder = $tableNameBuilder;
    }

    /**
     * {@inheritDoc}
     */
    protected function batchInsertPendingVersions(array $pendingVersions)
    {
        if (count($pendingVersions) === 0) {
            return;
        }

        $versionTable = $this->getVersionTable();
        $insert = sprintf('INSERT INTO %s ', $versionTable);

        $columns = $this->getVersionColumns();

        $placeholders = sprintf('(%s)', implode(',', array_fill(0, count($columns), '?')));
        $multiplePlaceholders = array_fill(0, count($pendingVersions), $placeholders);

        $params = [];
        foreach ($pendingVersions as $pendingVersion) {
            $params = array_merge($params, $this->getSQLParamsFromVersion($pendingVersion));
        }
        $values = implode(',', $multiplePlaceholders);

        $query = sprintf('%s(%s) VALUES %s', $insert, implode(',', $columns), $values);

        $this->connection->executeQuery($query, $params);
    }

    /**
     * Get the params from the version objects
     *
     * @param Version $version
     *
     * @return array
     */
    protected function getSQLParamsFromVersion(Version $version)
    {
        $params = [];
        $columns = $this->getVersionColumns();
        $metadata = $this->getVersionMetadata();

        foreach ($columns as $column) {
            $fieldName = $metadata->getFieldName($column);
            $fieldValue = $metadata->getFieldValue($version, $fieldName);
            if (is_array($fieldValue)) {
                $fieldValue = serialize($fieldValue);
            } elseif ($fieldValue instanceof \DateTime) {
                $date = new \DateTime($fieldValue->format(\DateTime::ISO8601));
                $date->setTimezone(new \DateTimeZone('UTC'));
                $fieldValue = $date->format('Y-m-d H:i:s');
            }
            $params[] = $fieldValue;
        }

        return $params;
    }

    /**
     * Get the class metadata object from entity
     *
     * @return ClassMetadata
     */
    protected function getVersionMetadata()
    {
        if (null === $this->versionMetadata) {
            $this->versionMetadata = $this->entityManager->getClassMetadata($this->versionClass);
        }

        return $this->versionMetadata;
    }

    /**
     * Get the table name for the Version entity
     *
     * @return string
     */
    protected function getVersionTable()
    {
        if (null === $this->versionTable) {
            $this->versionTable = $this->tableNameBuilder->getTableName($this->versionClass);
        }

        return $this->versionTable;
    }

    /**
     * Get the column names for the Version entity, ignoring
     * identifier column names (it will be calculated automatically)
     *
     * @return string
     */
    protected function getVersionColumns()
    {
        if (null === $this->versionColumns) {
            $metadata = $this->getVersionMetadata($this->versionClass);

            $this->versionColumns = array_diff(
                $metadata->getColumnNames(),
                $metadata->getIdentifierColumnNames()
            );
        }

        return $this->versionColumns;
    }
}
