<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Symfony\Component\HttpFoundation\Response;

final class LoadDatagridPimRoleGridIntegration extends ControllerIntegrationTestCase
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
            'pim_datagrid_load',
            [
                'alias' => 'pim-role-grid',
            ],
            'GET',
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json']
        );
        $content = json_decode($response->getContent(), true);
        $data = json_decode($content['data'], true);

        $expectedResponseRoles = [
            [
                'label' => 'Administrator',
                'id' => (string) $roleAdministrator->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleAdministrator->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleAdministrator->getId()]),
            ],
            [
                'label' => 'User',
                'id' => (string) $roleUser->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleUser->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleUser->getId()]),
            ],
            [
                'label' => 'Redactor',
                'id' => (string) $roleRedactor->getId(),
                'update_link' => $this->router->generate('pim_user_role_update', ['id' => $roleRedactor->getId()]),
                'delete_link' => $this->router->generate('pim_user_role_delete', ['id' => $roleRedactor->getId()]),
            ],
        ];

        $this->assertStatusCode($response, Response::HTTP_OK);
        self::assertCount(3, $data['data']);

        foreach ($expectedResponseRoles as $expectedResponseRole) {
            self::assertContains($expectedResponseRole, $data['data']);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
