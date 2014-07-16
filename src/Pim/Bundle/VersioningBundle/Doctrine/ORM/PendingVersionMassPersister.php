<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\Builder\VersionBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\Connection;

/**
 * Service to massively insert pending versions.
 * Useful for massive imports of products.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PendingVersionMassPersister
{
    /** @var VersionBuilder */
    protected $versionBuilder;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var Connection */
    protected $connection;

    /** @var EntityManager */
    protected $entityManager;

    /** @var string */
    protected $versionClass;

    /** @var string */
    protected $versionTable;

    /** @var array */
    protected $versionColumns;

    /** @var ClassMetadata */
    protected $versionMetadata;

    /**
     * @param VersionBuilder      $versionBuilder
     * @param VersionBuilder      $versionBuilder
     * @param NormalizerInterface $normalizer
     * @param Connection          $connection
     * @maram EntityManager       $entityManager
     * @param string              $versionClass
     */
    public function __construct(
        VersionBuilder $versionBuilder,
        VersionManager $versionManager,
        NormalizerInterface $normalizer,
        Connection $connection,
        EntityManager $entityManager,
        $versionClass
    ) {
        $this->versionBuilder = $versionBuilder;
        $this->versionManager = $versionManager;
        $this->normalizer     = $normalizer;
        $this->connection     = $connection;
        $this->entityManager  = $entityManager;
        $this->versionClass   = $versionClass;
    }

    /**
     * Create the pending versions for the products provided
     *
     * @param ProductInterface[] $products
     */
    public function persistPendingVersions(array $products)
    {
        $author = $this->versionManager->getUsername();
        $context = $this->versionManager->getContext();

        $pendingVersions = [];
        foreach ($products as $product) {
            $changeset = $this->normalizer->normalize($product, 'csv', ['versioning' => true]);
            $pendingVersions[] = $this->versionBuilder->createPendingVersion($product, $author, $changeset, $context);
        }
        $this->batchInsertPendingVersions($pendingVersions);
    }

    /**
     * Insert into pending versions
     *
     * @param array
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
            $this->versionTable = $this->getVersionMetadata()->getTableName();
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
