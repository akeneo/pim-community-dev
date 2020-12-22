<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_5_0_20201013070505_add_convert_to_simple_products_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const JOB_CODE = 'convert_to_simple_products';
    private const MIGRATION_LABEL = '_5_0_20201013070505_add_convert_to_simple_products_job';

    /**
     * @test
     */
    public function it_creates_the_job_instance()
    {
        $this->get('database_connection')->executeQuery(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => static::JOB_CODE]
        );
        $this->assertFalse($this->jobExists(self::JOB_CODE));

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertTrue($this->jobExists(self::JOB_CODE));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function jobExists(string $code): bool
    {
        $results = $this->get('database_connection')->executeQuery(
            'SELECT code FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $code]
        );

        return 0 < $results->rowCount();
    }
}
