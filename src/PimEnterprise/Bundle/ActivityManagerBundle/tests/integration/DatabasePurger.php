<?php

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use Akeneo\Test\Integration\DatabasePurger as BaseDatabasePurger;
use Pim\Behat\Context\DBALPurger;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabasePurger extends BaseDatabasePurger
{
    /**
     * Purges additional tables related to the activity manager.
     */
    public function purge()
    {
        $purger = new DBALPurger(
            $this->container->get('database_connection'),
            [
                'pimee_activity_manager_completeness_per_attribute_group',
                'pimee_activity_manager_project_product',
            ]
        );

        $purger->purge();

        parent::purge();
    }
}
