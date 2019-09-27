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
    /** @var KernelInterface */
    private $kernel;

    /** @var Application */
    private $cli;

    /** @var Connection */
    private $dbConnection;

    public function __construct(KernelInterface $kernel, Connection $dbConnection)
    {
        $this->kernel = $kernel;
        $this->cli = new Application($this->kernel);
        $this->cli->setAutoExit(false);
        $this->dbConnection = $dbConnection;
    }

    /**
     * Reset the schema by deleting all rows in the data.
     * Do note that is faster than dropping and creating the schema.
     *
     * @throws \RuntimeException
     */
    public function reset()
    {
        $schemaManager = $this->dbConnection->getSchemaManager();
        $tables = $schemaManager->listTableNames();

        $purger = new DBALPurger(
            $this->dbConnection,
            $tables,
            [
                'pim_catalog_product',
                'pim_catalog_product_model',
                'pim_catalog_group',
                'acl_security_identities',
                'pimee_security_product_category_access',
                'pimee_workflow_published_product',
                'oro_access_group'
            ]
        );
        $purger->purge();
    }
}
