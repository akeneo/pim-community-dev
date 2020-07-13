<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Query\FindSharedCatalogsQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\ORM\EntityManager;

class FindSharedCatalogsQueryIntegration extends TestCase
{
    /** @var FindSharedCatalogsQueryInterface */
    private $findSharedCatalogsQuery;

    /** @var EntityManager */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findSharedCatalogsQuery = $this->get(FindSharedCatalogsQueryInterface::class);
        $this->em = $this->get('doctrine')->getManager();
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

        self::assertEquals([
            [
                'code' => 'shared_catalog_1',
                'publisher' => 'system',
                'recipients' => [],
                'channel' => null,
                'catalogLocales' => [],
                'attributes' => [],
                'branding' => ['logo' => null],
            ],
        ], $results);
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

        self::assertEquals([
            [
                'code' => 'shared_catalog_1',
                'publisher' => 'system',
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
        ], $results);
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

    private function createJobInstance(
        string $code,
        string $jobName,
        string $type,
        int $status,
        array $rawParameters
    ): void {
        $jobInstance = new JobInstance();
        $jobInstance->setCode($code);
        $jobInstance->setLabel($code);
        $jobInstance->setJobName($jobName);
        $jobInstance->setStatus($status);
        $jobInstance->setConnector('Some connector name');
        $jobInstance->setType($type);
        $jobInstance->setRawParameters($rawParameters);

        $this->em->persist($jobInstance);
        $this->em->flush();
    }
}
