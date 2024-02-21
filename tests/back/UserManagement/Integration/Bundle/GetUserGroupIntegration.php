<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetUserGroupIntegration extends ControllerIntegrationTestCase
{
    public function test_it_gets_default_user_groups(): void
    {
        $this->logIn('admin');
        $response = $this->callApiRoute(
            'pim_user_user_group_rest_index',
            [],
            'GET',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json']
        );
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = \json_decode($response->getContent(), true);

        Assert::assertCount(4, $content);
        $groupNames = array_column($content, 'name');
        Assert::assertNotContains('do_not_show', $groupNames);
        Assert::assertContains('IT support', $groupNames);
        Assert::assertContains('Manager', $groupNames);
        Assert::assertContains('Redactor', $groupNames);
        Assert::assertContains('All', $groupNames);
    }

    public function setUp(): void
    {
        parent::setUp();

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
