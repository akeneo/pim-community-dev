<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_5_0_20200226102033_data_quality_insights_init_variant_products_evaluations extends AbstractMigration implements ContainerAwareInterface
{
    /** * @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $this->disableMigrationWarning();
        return;

        $createProductsCriteriaEvaluations = $this->container->get('akeneo.pim.automation.data_quality_insights.create_products_criteria_evaluations');
        foreach ($this->getVariantProductIdToEvaluateByBatch() as $productIds) {
            $createProductsCriteriaEvaluations->createAll($productIds);
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function getVariantProductIdToEvaluateByBatch(): \Generator
    {
        $batchSize = 100;

        $connection = $this->container->get('database_connection');
        $stmt = $connection->executeQuery(<<<SQL
SELECT id
FROM pim_catalog_product
WHERE product_model_id IS NOT NULL
AND is_enabled = 1
SQL
        );

        $productIds = [];
        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = new ProductId(intval($productId));

            if (count($productIds) >= $batchSize) {
                yield $productIds;
                $productIds = [];
            }
        }

        if (! empty($productIds)) {
            yield $productIds;
        }
    }

    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }

}
