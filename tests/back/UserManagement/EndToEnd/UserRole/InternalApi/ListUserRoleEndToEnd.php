<?php

namespace AkeneoTest\UserManagement\EndToEnd\UserGroup\InternalApi;


use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Tool\Component\Classification\Model\Category;
use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListUserRoleEndToEnd extends InternalApiTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $user = $this->getAdminUser();

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $user->setUILocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('de_DE'));
        $user->setCatalogLocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US'));

        $user->setCatalogScope($channel);
        $user->setDefaultTree(new Category());

        $this->authenticate($user);
    }

    public function testListUserRoles()
    {
        $user = $this->getAdminUser();

        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $user->setUILocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('de_DE'));
        $user->setCatalogLocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US'));

        $user->setCatalogScope($channel);
        $user->setDefaultTree(new Category());

        $this->authenticate($user);

        $this->client->request('GET', 'rest/user_role/');

        $apiUserRoles = <<<JSON
[{"id":4,"role":"ROLE_ADMINISTRATOR","label":"Administrator"},{"id":5,"role":"ROLE_CATALOG_MANAGER","label":"Catalog manager"},{"id":7,"role":"ROLE_TRAINEE","label":"Trainee"},{"id":6,"role":"ROLE_USER","label":"User"}]
JSON;

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiUserRoles, $response->getContent());

    }


    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
