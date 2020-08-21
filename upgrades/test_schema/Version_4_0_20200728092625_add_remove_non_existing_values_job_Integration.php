<?php


declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class Version_4_0_20200728092625_add_remove_non_existing_values_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const JOB_CODE = 'remove_non_existing_product_values';

    public function test_it_creates_remove_non_existing_values_job()
    {
        $this->getConnection()->executeQuery(
            'DELETE FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => static::JOB_CODE]
        );
        $this->assertFalse($this->jobExists( static::JOB_CODE));


        $this->reExecuteMigration($this->getMigrationLabel());
        $this->assertTrue($this->jobExists( static::JOB_CODE));
    }

    private function jobExists(string $code): bool
    {
        $results = $this->getConnection()->executeQuery(
            'SELECT code FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $code]
        );

        return 0 < $results->rowCount();
    }

    public function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
