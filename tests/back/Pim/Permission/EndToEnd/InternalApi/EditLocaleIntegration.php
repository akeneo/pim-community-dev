<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class EditLocaleIntegration extends WebTestCase
{
    private Connection $connection;

    /**
     * For example, the App feature adds user group of type 'app' instead of 'default', in order to be able to hide them
     * from the UI.
     */
    public function test_the_user_can_only_apply_permissions_on_default_type_user_group(): void
    {
        $this->get('feature_flags')->enable('permission');
        $this->authenticateAsAdmin();
        $id = $this->connection->fetchOne('SELECT id FROM pim_catalog_locale WHERE is_activated = 1 LIMIT 1');
        $this->client->request(
            'GET',
            sprintf('/configuration/locale/%s/edit', $id),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertFalse(str_contains($response->getContent(), 'do_not_show_user_group'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');

        /** @var ObjectManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $connectionGroup = new Group('do_not_show_user_group');
        $connectionGroup->setType('another_type');
        $entityManager->persist($connectionGroup);
        $entityManager->flush();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
