<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\TestEnterprise\Integration;

use Akeneo\Test\Integration\TestCase as BaseTestCase;
use Pim\Behat\Context\DBALPurger;

abstract class TestCase extends BaseTestCase
{
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
