<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations;

use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractQuantifiedAssociationsTestCase extends InternalApiTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->getAdminUser());
        $this->createQuantifiedAssociationType('PRODUCTSET');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    protected function createQuantifiedAssociationType(string $code): void
    {
        $data =
            <<<JSON
    {
        "code": "$code",
        "is_quantified": true
    }
JSON;
        $this->client->request(
            'POST',
            '/configuration/association-type/rest',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data
        );
    }

    protected function getAdminUser(): UserInterface
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
