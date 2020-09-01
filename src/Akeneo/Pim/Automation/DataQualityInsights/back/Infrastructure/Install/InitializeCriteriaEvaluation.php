<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

final class InitializeCriteriaEvaluation
{
    private const BATCH_OF_PRODUCTS = 100;

    /** @var FeatureFlag */
    private $featureFlag;

    /** @var Connection */
    private $db;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

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

        $query = $this->db->executeQuery('select count(*) as nb from pim_catalog_product where product_model_id is null');
        $nb = $query->fetch();

        if ($nb['nb']===0) {
            return;
        }

        $steps = ceil($nb['nb']/intval(self::BATCH_OF_PRODUCTS));

        for ($i = 0; $i<$steps; $i++) {
            $stmt = $this->db->query('select id from pim_catalog_product where product_model_id is null LIMIT ' . $i*intval(self::BATCH_OF_PRODUCTS) . ',' . intval(self::BATCH_OF_PRODUCTS));
            $ids = array_map(function ($id) {
                return intval($id);
            }, $stmt->fetchAll(FetchMode::COLUMN, 0));

            $productIds = array_map(function ($id) {
                return new ProductId($id);
            }, $ids);

            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }
}
