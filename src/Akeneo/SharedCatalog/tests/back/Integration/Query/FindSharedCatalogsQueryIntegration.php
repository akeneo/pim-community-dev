<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindSharedCatalogsQueryInterface;
use Akeneo\SharedCatalog\tests\back\Utils\AuthenticateAs;
use Akeneo\SharedCatalog\tests\back\Utils\CreateJobInstance;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class FindSharedCatalogsQueryIntegration extends TestCase
{
    use CreateJobInstance;
    use AuthenticateAs;

    /** @var FindSharedCatalogsQueryInterface */
    private $findSharedCatalogsQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findSharedCatalogsQuery = $this->get(FindSharedCatalogsQueryInterface::class);
        $this->authenticateAs('admin');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_fetches_a_shared_catalog_without_any_parameters_configured()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_READY,
            []
        );

        $results = $this->findSharedCatalogsQuery->execute();
        $normalizedResults = array_map(function (SharedCatalog $sharedCatalog) {
            return $sharedCatalog->normalizeForExternalApi();
        }, $results);

        self::assertEquals([
            [
                'code' => 'shared_catalog_1',
                'publisher' => 'admin@example.com',
                'recipients' => [],
                'channel' => null,
                'catalogLocales' => [],
                'attributes' => [],
                'branding' => ['logo' => null],
            ],
        ], $normalizedResults);
    }

    /**
     * @test
     */
    public function it_fetches_a_shared_catalog_with_all_parameters_configured()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_READY,
            [
                'recipients' => [
                    [
                        'email' => 'betty@akeneo.com',
                    ],
                    [
                        'email' => 'julia@akeneo.com',
                    ],
                ],
                'filters' => [
                    'structure' => [
                        'scope' => 'mobile',
                        'locales' => [
                            'en_US',
                        ],
                        'attributes' => [
                            'name',
                        ],
                    ],
                ],
                'branding' => [
                    'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
                ],
            ]
        );

        $results = $this->findSharedCatalogsQuery->execute();
        $normalizedResults = array_map(function (SharedCatalog $sharedCatalog) {
            return $sharedCatalog->normalizeForExternalApi();
        }, $results);

        self::assertEquals([
            [
                'code' => 'shared_catalog_1',
                'publisher' => 'admin@example.com',
                'recipients' => [
                    'betty@akeneo.com',
                    'julia@akeneo.com',
                ],
                'channel' => 'mobile',
                'catalogLocales' => [
                    'en_US',
                ],
                'attributes' => [
                    'name',
                ],
                'branding' => [
                    'logo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABKoAAAJFCAYAAAD9Ih9',
                ],
            ],
        ], $normalizedResults);
    }

    /**
     * @test
     */
    public function it_ignores_shared_catalogs_drafts()
    {
        $this->createJobInstance(
            'shared_catalog_1',
            'akeneo_shared_catalog',
            'export',
            JobInstance::STATUS_DRAFT,
            []
        );

        $results = $this->findSharedCatalogsQuery->execute();

        self::assertEmpty($results);
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

        $results = $this->findSharedCatalogsQuery->execute();

        self::assertEmpty($results);
    }
}
