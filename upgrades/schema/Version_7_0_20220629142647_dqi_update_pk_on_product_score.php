<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class Version_7_0_20220629142647_dqi_update_pk_on_product_score extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $this->isProductUuidAlreadyPrimaryAndNotEvaluatedAt($schema),
            'product_uuid column is already the primary key of pim_data_quality_insights_product_score and no more evaluated_at'
        );

        $this->runCommand('pim:data-quality-insights:clean-product-scores');

        $this->addSql('ALTER TABLE pim_data_quality_insights_product_score DROP PRIMARY KEY, ADD PRIMARY KEY (product_uuid), ALGORITHM=INPLACE, LOCK=NONE;');

        $this->runCommand('pim:data-quality-insights:populate-product-models-scores-and-ki');
    }

    private function isProductUuidAlreadyPrimaryAndNotEvaluatedAt(Schema $schema): bool
    {
        $productScoreTable = $schema->getTable('pim_data_quality_insights_product_score');

        $isProductUuidPrimary = in_array('product_uuid', $productScoreTable->getPrimaryKeyColumns());
        $isEvaluatedAtNotAPrimary = !in_array('evaluated_at', $productScoreTable->getPrimaryKeyColumns());

        return $isProductUuidPrimary && $isEvaluatedAtNotAPrimary;
    }

    private function runCommand(string $commandName) {
        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $commandName,
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (Command::SUCCESS !== $exitCode) {
            throw new \Exception(sprintf('Migration failed: %s', $output->fetch()));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
