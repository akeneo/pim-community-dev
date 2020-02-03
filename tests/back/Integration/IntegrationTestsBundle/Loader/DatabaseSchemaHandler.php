<?php

namespace Akeneo\Test\IntegrationTestsBundle\Loader;

use Doctrine\DBAL\Connection;
use Pim\Behat\Context\DBALPurger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseSchemaHandler
{
    /** @var Connection */
    private $dbConnection;

    /** @var array */
    private static $tablesToTruncate = [
        'pim_catalog_product',
        'pim_catalog_product_model',
        'pim_catalog_group',
        'acl_security_identities',
        'pimee_security_product_category_access',
        'pimee_workflow_published_product',
        'oro_access_group',
    ];

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Reset the schema by deleting all rows in the data.
     * Do note that is faster than dropping and creating the schema.
     *
     * We avoid to truncate EE tables in CE (with array_intersect), otherwise the purger will fail at the first
     * EE table to truncate without raising any error (no way to catch it).
     *
     * @throws \RuntimeException
     */
    public function reset()
    {
        $schemaManager = $this->dbConnection->getSchemaManager();
        $tables = array_merge($schemaManager->listTableNames(), ['pim_session']);

        $purger = new DBALPurger(
            $this->dbConnection,
            array_diff($tables, self::$tablesToTruncate),
            array_intersect($tables, self::$tablesToTruncate)
        );
        $purger->purge();
    }
}
