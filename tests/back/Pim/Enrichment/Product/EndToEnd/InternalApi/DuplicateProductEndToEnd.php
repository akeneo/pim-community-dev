<?php

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\EndToEnd\InternalAPI;

use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DuplicateProductEndToEnd extends InternalApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->getAdminUser());
    }

    public function test_it_duplicates_a_product()
    {
        $url = $this->getRouter()->generate('pimee_enrich_product_rest_duplicate', [
            'id' => 'duplicate_product'
        ]);

        $this->client->request('POST', $url);

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getRouter(): RouterInterface
    {
        return self::$container->get('router');
    }

    private function getAdminUser(): UserInterface
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
