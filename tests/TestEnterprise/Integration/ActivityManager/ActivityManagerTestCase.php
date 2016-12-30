<?php

namespace TestEnterprise\Integration\ActivityManager;

use Pim\Behat\Context\DBALPurger;
use Test\Integration\TestCase;

class ActivityManagerTestCase extends TestCase
{
    /** {@inheritdoc} */
    protected $catalog = 'activity_manager';

    /**
     * {@inheritdoc}
     */
    protected function purgeDatabase()
    {
        $purger = new DBALPurger(
            $this->get('database_connection'),
            [
                'pimee_activity_manager_completeness_per_attribute_group',
                'pimee_activity_manager_project_product',
            ]
        );

        $purger->purge();

        parent::purgeDatabase();
    }
}
