<?php

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration;

use Akeneo\Test\Integration\DatabaseSchemaHandler as BaseDatabaseSchemaHandler;
use Pim\Behat\Context\DBALPurger;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseSchemaHandler extends BaseDatabaseSchemaHandler
{
    /**
     * Purges additional tables related to the teamwork assistant.
     */
    public function reset()
    {
        $purger = new DBALPurger(
            $this->kernel->getContainer()->get('database_connection'),
            [
                'pimee_teamwork_assistant_completeness_per_attribute_group',
                'pimee_teamwork_assistant_project_product',
            ]
        );

        $purger->purge();

        parent::reset();
    }
}
