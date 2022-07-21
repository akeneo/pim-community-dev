<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Ramsey\Uuid\Uuid;


class RemoveCompletenessWhenDeletingProductIntegration extends AbstractCompletenessTestCase
{
    public function testCompletenessIsUpdatedWhenProductIsDeleted()
    {
        $product1 = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('my_product1');
        if ($product1 === null) {
            throw new \RuntimeException("Could not fetch a product identifier='my_product1'.");
        }

        $product2 = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('my_product2');
        if ($product2 === null) {
            throw new \RuntimeException("Could not fetch a product identifier='my_product2'.");
        }

        $uuid = $product1->getUuid();
        $completeness = $this->getProductCompletenesses()
            ->fromProductUuid($uuid);
        $this->assertEquals(1, $completeness->count());
        $this->get('pim_catalog.remover.product')
            ->remove($product1);
        $completeness = $this->getProductCompletenesses()
            ->fromProductUuid($uuid);
        $this->assertEquals(0, $completeness->count());
        $completeness = $this->getProductCompletenesses()
            ->fromProductUuid($product2->getUuid());
        $this->assertEquals(1, $completeness->count());
    }

    public function testCompletenessIsUpdatedWhenProductsAreDeleted()
    {
        $products = $this->get('pim_catalog.repository.product')
            ->findAll();

        if (count($products) !== 3) {
            throw new \RuntimeException("There should be 3 products.");
        }
        // remove product with even ID from deletion scheme
        $productsToDelete = array_filter($products, function($p) {
            return $p->getIdentifier() === 'my_product2';
        });
        // create a map of expected completeness
        $expectedCompleteness = [];
        foreach ($products as $product) {
            $expectedCompleteness[$product->getUuid()->toString()] =
                $product->getIdentifier() === 'my_product2' ? 0 : 1;
        }

        // mass remove all products in deletion scheme
        $this->get('pim_catalog.remover.product')
            ->removeAll($productsToDelete);

        foreach ($expectedCompleteness as $uuid => $cplt) {
            $this->assertEquals(
                $cplt,
                $this->getProductCompletenesses()->fromProductUuid(Uuid::fromString($uuid))->count(),
                sprintf("Check completeness of product %s is %d.", $uuid, $cplt)
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $adminUserId = $this->createAdminUser()->getId();
        $family = $this->get('pim_catalog.factory.family')->create();
        $family->setCode('family');
        $this->get('pim_catalog.saver.family')->save($family);
        $this->createAttribute('a_text', AttributeTypes::BOOLEAN);
        $this->createProduct(
            'my_product1',
            [
                new SetFamily('family'),
                new SetBooleanValue('a_text', null, null, true)
            ],
            $adminUserId
        );
        $this->createProduct(
            'my_product2',
            [
                new SetFamily('family'),
                new SetBooleanValue('a_text', null, null, true)
            ],
            $adminUserId
        );
        $this->createProduct(
            'my_product3',
            [
                new SetFamily('family'),
                new SetBooleanValue('a_text', null, null, true)
            ],
            $adminUserId
        );
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents, int $userId): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $userId,
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }
}
