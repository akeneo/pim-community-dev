<?php

namespace Akeneo\SharedCatalog\tests\back\EndToEnd\ExternalApi;

use Akeneo\SharedCatalog\tests\back\Utils\AuthenticateAs;
use Akeneo\SharedCatalog\tests\back\Utils\CreateJobInstance;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class SharedCatalogListActionEndToEnd extends ApiTestCase
{
    use CreateJobInstance;
    use AuthenticateAs;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAs('admin');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_list_the_shared_catalogs(): void
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

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/shared-catalogs');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [
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
            ],
            json_decode($response->getContent(), true)
        );
    }
}
