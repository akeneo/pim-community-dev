<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Query\FindSharedCatalogQueryInterface;
use Akeneo\SharedCatalog\tests\back\Utils\AuthenticateAs;
use Akeneo\SharedCatalog\tests\back\Utils\CreateJobInstance;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class FindSharedCatalogQueryIntegration extends TestCase
{
    use CreateJobInstance;
    use AuthenticateAs;

    /** @var FindSharedCatalogQueryInterface */
    private $findSharedCatalogQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findSharedCatalogQuery = $this->get(FindSharedCatalogQueryInterface::class);
        $this->authenticateAs('admin');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_fetches_a_shared_catalog()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_READY,
            []
        );

        $result = $this->findSharedCatalogQuery->find('shared_catalog_1');
        $normalizedResult = $result->normalizeForExternalApi();

        self::assertEquals([
            'code' => 'shared_catalog_1',
            'publisher' => 'admin@example.com',
            'recipients' => [],
            'channel' => null,
            'catalogLocales' => [],
            'attributes' => [],
            'branding' => ['logo' => null],
        ], $normalizedResult);
    }

    /**
     * @test
     */
    public function it_ignores_other_job_instances()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'something_else',
            'export',
            JobInstance::STATUS_READY,
            []
        );

        $result = $this->findSharedCatalogQuery->find('shared_catalog_1');

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function it_returns_null_if_code_does_not_exists()
    {
        $result = $this->findSharedCatalogQuery->find('this_does_not_exists');

        self::assertNull($result);
    }
}
