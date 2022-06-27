<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class InitializeCriteriaEvaluation
{
    private const BATCH_OF_PRODUCTS = 100;

    private FeatureFlag $featureFlag;
    private Connection $db;
    private CreateCriteriaEvaluations $createProductsCriteriaEvaluations;

    public function __construct(
        FeatureFlag $featureFlag,
        Connection $db,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->featureFlag = $featureFlag;
        $this->db = $db;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
    }

    public function initialize()
    {
        if (false === $this->featureFlag->isEnabled()) {
            throw new \RuntimeException(
                'Data Quality Insights Feature is not enabled. This migration script is skipped.'
            );
        }

        $queryReslt = $this->db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null');
        $nb = $queryReslt->fetchAssociative();

        if ($nb['nb']===0) {
            return;
        }

        $steps = ceil($nb['nb']/intval(self::BATCH_OF_PRODUCTS));

        for ($i = 0; $i<$steps; $i++) {
            $stmt = $this->db->executeQuery('select BIN_TO_UUID(uuid) AS uuid from pim_catalog_product where product_model_id is null LIMIT ' . $i*intval(self::BATCH_OF_PRODUCTS) . ',' . intval(self::BATCH_OF_PRODUCTS));
            $uuids = array_map(function ($uuid) {
                return Uuid::fromString($uuid);
            }, $stmt->fetchFirstColumn());

            $productUuids = array_map(function (UuidInterface $uuid) {
                return ProductUuid::fromUuid($uuid);
            }, $uuids);

            $this->createProductsCriteriaEvaluations->createAll(ProductUuidCollection::fromProductUuids($productUuids));
        }
    }
}
