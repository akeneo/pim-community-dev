<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20190911121924_proposal_add_source_to_product_drafts extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pimee_workflow_product_draft ADD source VARCHAR(255) NOT NULL DEFAULT \'\', ADD source_label VARCHAR(255) NOT NULL DEFAULT \'\', ADD author_label VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE pimee_workflow_product_model_draft ADD source VARCHAR(255) NOT NULL DEFAULT \'\', ADD source_label VARCHAR(255) NOT NULL DEFAULT \'\', ADD author_label VARCHAR(255) NOT NULL DEFAULT \'\'');

        $this->populateNewColumns();
   }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function populateNewColumns(): void
    {
        foreach (['pimee_workflow_product_draft', 'pimee_workflow_product_model_draft'] as $draftTable) {
            // PIM users
            $this->addSql(
                "UPDATE $draftTable INNER JOIN oro_user ON oro_user.username = author SET author_label = COALESCE(CONCAT(oro_user.first_name, ' ', oro_user.last_name), ''), source = :pimSourceCode, source_label = :pimSourceLabel",
                [
                    'pimSourceCode' => PimUserDraftSourceFactory::PIM_SOURCE_CODE,
                    'pimSourceLabel' => PimUserDraftSourceFactory::PIM_SOURCE_LABEL,
                ]
            );

            // Onboarder
            if ($this->isOnboarderEnabled()) {
                $this->addSql("UPDATE $draftTable INNER JOIN supplier ON supplier.id = author SET author_label = supplier.name, source = 'onboarder', source_label = 'Onboarder'");
            }

            // Default (to handle errors)
            $this->addSql("UPDATE $draftTable SET author_label = author WHERE author_label = ''");
            $this->addSql(
                "UPDATE $draftTable SET source = :pimSourceCode, source_label = :pimSourceLabel WHERE source = ''",
                [
                    'pimSourceCode' => PimUserDraftSourceFactory::PIM_SOURCE_CODE,
                    'pimSourceLabel' => PimUserDraftSourceFactory::PIM_SOURCE_LABEL,
                ]
            );
        }
    }

    private function isOnboarderEnabled(): bool
    {
        $sql = "SELECT EXISTS(SELECT 1 FROM information_schema.tables WHERE TABLE_NAME = 'supplier')";

        return (bool) $this->connection->fetchColumn($sql);
    }
}
