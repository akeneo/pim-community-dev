<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This migration disable read-only attribute when feature flag "read_only_product_attribute" is disabled
 */
final class Version_7_0_20221004090000_fix_read_only_attribute extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $isReadOnlyFeatureAvailableAndEnabled = $this->isReadOnlyFeatureAvailableAndEnabled();
        if ( $isReadOnlyFeatureAvailableAndEnabled ) {
            return;
        }

        $sql = <<<SQL
            UPDATE `pim_catalog_attribute`
            SET `properties` = REPLACE(`properties`, '"is_read_only";b:1;', '"is_read_only";b:0;')
        SQL;

        $this->addSql($sql);
    }

    private function isReadOnlyFeatureAvailableAndEnabled(): bool
    {
        try {
            return $this->container->get('feature_flags')->isEnabled('read_only_product_attribute');
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
