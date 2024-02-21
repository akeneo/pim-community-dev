<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\Group;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DatagridControllerIntegration extends ControllerIntegrationTestCase
{
    public function test_it_shows_in_datagrid_only_default_groups(): void
    {
        $this->logIn('julia');
        $this->callApiRoute(
            $this->client,
            'pim_datagrid_load',
            ['alias' => 'pim-user-group-grid'],
            Request::METHOD_GET
        );

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $content = \json_decode($response->getContent(), true);
        $groups = \json_decode($content['data'], true);
        Assert::assertCount(3, $groups['data']);
        $groupNames = array_column($groups['data'], 'name');
        Assert::assertNotContains('do_not_show', $groupNames);
        Assert::assertContains('IT support', $groupNames);
        Assert::assertContains('Manager', $groupNames);
        Assert::assertContains('Redactor', $groupNames);
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
