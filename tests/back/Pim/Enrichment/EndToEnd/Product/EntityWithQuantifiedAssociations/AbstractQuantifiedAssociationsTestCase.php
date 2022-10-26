<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations;

use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractQuantifiedAssociationsTestCase extends InternalApiTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

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

    protected function getAdminUser(): UserInterface
    {
        return self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    protected function getProductUuid(string $identifier): ?string
    {
        return $this->get('database_connection')->executeQuery(
                'SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier = :identifier',
                ['identifier' => $identifier]
            )->fetchOne() ?: null;
    }
}
