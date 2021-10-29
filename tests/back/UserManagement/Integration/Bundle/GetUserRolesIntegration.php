<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Symfony\Component\HttpFoundation\Response;

final class GetUserRolesIntegration extends ControllerIntegrationTestCase
{
    /**
     * @group ce
     */
    public function test_it_only_returns_default_type_roles(): void
    {
        $roleRepository = $this->get('pim_user.repository.role');
        $roleSaver = $this->get('pim_user.saver.role');

        /** @var RoleInterface $roleRedactor */
        $roleRedactor = $this->get('pim_user.factory.role')->create();
        $roleRedactor->setRole('ROLE_REDACTOR');
        $roleRedactor->setLabel('Redactor');

        $roleSaver->save($roleRedactor);

        /** @var RoleInterface $roleCatalogManager */
        $roleCatalogManager = $roleRepository->findOneByIdentifier('ROLE_CATALOG_MANAGER');
        $roleCatalogManager->setType('some_other_type');

        $roleSaver->save($roleCatalogManager);

        /** @var RoleInterface $roleUser */
        $roleUser = $roleRepository->findOneByIdentifier('ROLE_USER');

        /** @var RoleInterface $roleAdministrator */
        $roleAdministrator = $roleRepository->findOneByIdentifier('ROLE_ADMINISTRATOR');

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_role_rest_index',
            [],
            'GET',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json']
        );
        $content = json_decode($response->getContent(), true);

        $expectedResponseRoles = [
            [
                'id' => $roleAdministrator->getId(),
                'role' => 'ROLE_ADMINISTRATOR',
                'label' => 'Administrator',
                'type' => 'default',
            ],
            [
                'id' => $roleUser->getId(),
                'role' => 'ROLE_USER',
                'label' => 'User',
                'type' => 'default',
            ],
            [
                'id' => $roleRedactor->getId(),
                'role' => 'ROLE_REDACTOR',
                'label' => 'Redactor',
                'type' => 'default',
            ],
        ];

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertCount(3, $content);

        foreach ($expectedResponseRoles as $expectedResponseRole) {
            self::assertContains($expectedResponseRole, $content);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
