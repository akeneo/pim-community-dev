<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifier;
use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifierHandler;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ChangeMainIdentifierIntegration extends TestCase
{
    public function testChangeMainIdentifier()
    {
        $handler = $this->get(ChangeMainIdentifierHandler::class);
        $attributeRepository = $this->get('pim_catalog.repository.attribute');

        // In the inital state of the catalog, the sku will have been defied as the main identifier
        Assert::assertEquals('sku', $this->getMainIdentifierCode());

        $this->createNewIdentifierAttribute('my_new_identifier');

        $command = new ChangeMainIdentifier('my_new_identifier');
        ($handler)($command);

        Assert::assertEquals('my_new_identifier', $this->getMainIdentifierCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createNewIdentifierAttribute(string $code): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $code,
            'type' => AttributeTypes::IDENTIFIER,
            'group' => 'other',
            'useable_as_grid_filter' => true,
        ]);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function getMainIdentifierCode(): string
    {
        $sql = <<<'SQL'
SELECT code from pim_catalog_attribute WHERE main_identifier=1 LIMIT 1;
SQL;

        $connection = $this->get('database_connection');
        return $connection->executeQuery($sql)->fetchOne();
    }
}
