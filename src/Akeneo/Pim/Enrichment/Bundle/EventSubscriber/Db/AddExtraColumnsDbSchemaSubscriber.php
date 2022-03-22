<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Db;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @TODO: CPM-507 remove this class
 * During the migration of product id to uuid, we have a moment where both "foreign columns" need to exist.
 * For instance pim_catalog_association_product_model_to_product.product_id and pim_catalog_association_product_model_to_product.product_uuid
 * We change the doctrine config to use product_uuid, but we can't declare the product_id. So we add it in this class.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddExtraColumnsDbSchemaSubscriber implements EventSubscriberInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'addExtraColumns',
        ];
    }

    public function addExtraColumns(): void
    {
        $sql = <<<SQL
        ALTER TABLE pim_catalog_association ADD COLUMN owner_id int NOT NULL AFTER id;
        ALTER TABLE pim_catalog_association_product_model_to_product ADD COLUMN product_id int NOT NULL;
SQL;

        $this->connection->executeQuery($sql);
    }
}
