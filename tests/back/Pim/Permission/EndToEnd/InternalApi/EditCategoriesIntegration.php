<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class EditCategoriesIntegration extends WebTestCase
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
        $id = $this->connection->fetchOne('SELECT id FROM pim_catalog_category WHERE code = "master"');
        $this->client->request(
            'GET',
            sprintf('/enrich/product-category-tree/%s/edit', $id),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $result = json_decode($response->getContent(), true);
        $viewPermissionChoices = $result['form']['permissions']['view']['choices'];
        $editPermissionChoices = $result['form']['permissions']['edit']['choices'];
        $ownPermissionChoices = $result['form']['permissions']['own']['choices'];
        $groupNames = array_unique(
            array_merge(
                array_column($viewPermissionChoices, 'label'),
                array_column($editPermissionChoices, 'label'),
                array_column($ownPermissionChoices, 'label'),
            )
        );

        Assert::assertCount(4, $groupNames);
        Assert::assertNotContains('do_not_show', $groupNames);
        Assert::assertContains('IT support', $groupNames);
        Assert::assertContains('Manager', $groupNames);
        Assert::assertContains('Redactor', $groupNames);
        Assert::assertContains('All', $groupNames);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');

        /** @var ObjectManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $connectionGroup = new Group('do_not_show');
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
