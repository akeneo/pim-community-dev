<?php

namespace AkeneoTest\UserManagement\EndToEnd\UserGroup\InternalApi;


use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListUserGroupEndToEnd extends InternalApiTestCase
{
    public function testListUserGroups()
    {
        $this->authenticate($this->getAdminUser());

        $this->client->request('GET', 'rest/user_group/');

        $apiUserGroups = <<<JSON
[{"name":"All","meta":{"id":4,"default":true}},{"name":"IT support","meta":{"id":1,"default":false}},{"name":"Manager","meta":{"id":2,"default":false}},{"name":"Redactor","meta":{"id":3,"default":false}}]
JSON;

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiUserGroups, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
