<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

class SqlGetTransformations implements GetTransformations
{
    private Connection $connection;

    private TransformationCollectionFactory $transformationCollectionFactory;

    public function __construct(
        Connection $connection,
        TransformationCollectionFactory $transformationCollectionFactory
    ) {
        $this->connection = $connection;
        $this->transformationCollectionFactory = $transformationCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fromAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): TransformationCollection
    {
        $sql = <<<SQL
SELECT transformations
FROM akeneo_asset_manager_asset_family
WHERE identifier = :identifier;
SQL;

        $normalizedTransformations = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => (string)$assetFamilyIdentifier,
            ]
        )->fetchColumn();

        if (false === $normalizedTransformations) {
            throw AssetFamilyNotFoundException::withIdentifier($assetFamilyIdentifier);
        }

        return $this->transformationCollectionFactory->fromDatabaseNormalized(
            Type::getType(Types::JSON)->convertToPHPValue(
                $normalizedTransformations,
                $this->connection->getDatabasePlatform()
            )
        );
    }
}
