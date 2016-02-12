<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add precision to the PublishedProductMetric entity to be able to save data and baseData for numbers under 1E-4.
 * We need this precision, for example, when we convert data in millimeter cube to meter cube (1E-8).
 * For data which are already saved, as many 0 as needed will be added at the end of the number.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class Version_1_5_20151202163719_metric_precision extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $upgradeHelper = new UpgradeHelper($this->container);
        if ($upgradeHelper->areProductsStoredInMongo()) {
            return;
        }

        $this->addSql('ALTER TABLE pimee_workflow_published_product_metric CHANGE data data NUMERIC(24, 12) DEFAULT NULL, CHANGE base_data base_data NUMERIC(24, 12) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
