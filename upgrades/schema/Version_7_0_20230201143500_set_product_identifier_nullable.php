<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220516171405SetProductIdentifierNullableZddMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_7_0_20230201143500_set_product_identifier_nullable extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    public function up(Schema $schema): void
    {
        $this->getMigration()->migrateNotZdd();
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getMigration(): V20220516171405SetProductIdentifierNullableZddMigration
    {
        return $this->container->get('Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220516171405SetProductIdentifierNullableZddMigration');
    }
}
