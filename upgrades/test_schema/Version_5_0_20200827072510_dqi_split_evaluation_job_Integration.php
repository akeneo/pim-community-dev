<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_5_0_20200827072510_dqi_split_evaluation_job_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    public function test_it_adds_job(): void
    {
        $this->clean();

        $this->assertFalse($this->jobExists());

        $this->reExecuteMigration('_5_0_20200827072510_dqi_split_evaluation_job');

        $this->assertTrue($this->jobExists());
    }

    private function clean(): void
    {
        $this->get('database_connection')
            ->executeQuery( "DELETE FROM akeneo_batch_job_instance WHERE code = 'data_quality_insights_prepare_evaluations'");

    }

    private function jobExists(): bool
    {
        $results = $this->get('database_connection')
            ->executeQuery("SELECT code FROM akeneo_batch_job_instance WHERE code = 'data_quality_insights_prepare_evaluations'");

        return 0 < $results->rowCount();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
