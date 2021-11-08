<?php

namespace Akeneo\Platform\Job\Test\Integration\Infrastructure\Registry;

use Akeneo\Platform\Job\Test\Integration\IntegrationTestCase;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class NotVisibleJobsRegistryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_return_not_visible_jobs_codes(): void
    {
        $notVisibleJobsRegistry = $this->get('Akeneo\Platform\Job\Infrastructure\Registry\NotVisibleJobsRegistry');

        $expectedJobCodes = [
            'another_product_export',
            'prepare_evaluation'
        ];

        $this->assertEqualsCanonicalizing($expectedJobCodes, $notVisibleJobsRegistry->getCodes());
    }
}
