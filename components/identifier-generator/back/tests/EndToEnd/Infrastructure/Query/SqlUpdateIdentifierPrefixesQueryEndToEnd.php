<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\EndToEndTestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

class SqlUpdateIdentifierPrefixesQueryEndToEnd extends EndToEndTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_should_generate_prefixes_on_save(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), [
            'my_identifier_' => '123',
            'my_identifier_1' => '23',
            'my_identifier_12' => '3',
        ]);
    }

    /** @test */
    public function it_should_update_prefixes_on_update(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        $this->updateProductIdentifier($productFromDatabase, 'my_new_identifier_234_567');
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), [
            'my_new_identifier_' => '234',
            'my_new_identifier_2' => '34',
            'my_new_identifier_23' => '4',
            'my_new_identifier_234_' => '567',
            'my_new_identifier_234_5' => '67',
            'my_new_identifier_234_56' => '7',
        ]);
    }

    /** @test */
    public function it_should_remove_all_prefixes_on_update_if_new_identifier_does_not_have_digits(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        $this->updateProductIdentifier($productFromDatabase, 'identifier_without_digits');
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), []);
    }

    /** @test */
    public function it_should_remove_prefixes_on_delete(): void
    {
        $productFromDatabase = $this->createProduct('my_identifier_123');
        $this->deleteProduct($productFromDatabase);
        Assert::assertEquals($this->getPrefixes($productFromDatabase->getUuid(), $this->getIdentifierId()), []);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function getPrefixes(UuidInterface $productUuid, $identifierId): array
    {
        $sql = <<<SQL
SELECT `prefix`, `number`
FROM pim_catalog_identifier_generator_prefixes
WHERE product_uuid=UUID_TO_BIN("%s")
AND attribute_id=%d
SQL;

        return $this->getConnection()->fetchAllKeyValue(\sprintf($sql, $productUuid->toString(), $identifierId));
    }

    private function getIdentifierId(): int
    {
        $sql = <<<SQL
SELECT `id`
FROM pim_catalog_attribute
WHERE attribute_type='pim_catalog_identifier'
SQL;

        return \intval($this->getConnection()->fetchOne($sql));
    }

    private function deleteProduct(ProductInterface $product): void
    {
        $this->getProductRemover()->remove($product);
    }
}
