<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierCommand;
use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierHandler;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchMainIdentifierIntegration extends TestCase
{
    const FORMER_CODE = 'sku';
    const NEW_CODE = 'newIdentifier';

    public function test_it_switches_main_identifier()
    {
        $this->createIdentifierAttribute(self::NEW_CODE);

        $this->switchIdentifiers();

        Assert::assertTrue($this->isMainIdentifier(self::NEW_CODE));
        Assert::assertFalse($this->isMainIdentifier(self::FORMER_CODE));
    }

    public function test_it_updates_updated_field()
    {
        $this->createIdentifierAttribute(self::NEW_CODE);
        $this->updateUpdatedField([self::FORMER_CODE, self::NEW_CODE]);
        $formerUpdatedField = $this->getUpdatedField(self::FORMER_CODE);
        $newUpdatedField = $this->getUpdatedField(self::NEW_CODE);

        $this->switchIdentifiers();

        Assert::assertNotEquals(
            $this->getUpdatedField(self::FORMER_CODE),
            $formerUpdatedField
        );
        Assert::assertNotEquals(
            $this->getUpdatedField(self::NEW_CODE),
            $newUpdatedField
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createIdentifierAttribute(
        string $code
    ): void {
        $attribute = $this->getAttributeBuilder()->build([
            'code' => $code,
            'type' => AttributeTypes::IDENTIFIER,
            'group' => AttributeGroupInterface::DEFAULT_CODE,
            'useable_as_grid_filter' => true,
        ], true);
        $this->getAttributeSaver()->save($attribute);
    }

    private function getAttributeSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getAttributeBuilder(): EntityBuilder
    {
        return $this->get('akeneo_integration_tests.base.attribute.builder');
    }

    private function getCommandHandler(): SwitchMainIdentifierHandler
    {
        return $this->get(SwitchMainIdentifierHandler::class);
    }

    private function isMainIdentifier(
        string $attributeCode
    ): bool {
        $sql = <<<SQL
SELECT main_identifier
FROM pim_catalog_attribute
WHERE code = :attributeCode
SQL;

        return $this->getConnection()->fetchOne(
            $sql,
            ['attributeCode' => $attributeCode]
            ) === '1';
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getUpdatedField(
        string $attributeCode
    ): string {
        $sql = <<<SQL
SELECT updated
FROM pim_catalog_attribute
WHERE code = :attributeCode
SQL;

        return $this->getConnection()->fetchOne(
            $sql,
            ['attributeCode' => $attributeCode]
        );
    }

    /**
     * @param string[] $attributeCodes
     */
    private function updateUpdatedField(
        array $attributeCodes
    ): void {
        $sql = <<<SQL
UPDATE pim_catalog_attribute
SET updated = ADDTIME(NOW(), "-2:0:0")
WHERE code IN (:attributeCodes)
SQL;

        $this->getConnection()->executeQuery(
            $sql,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY],
        );
    }

    private function switchIdentifiers(): void
    {
        $command = SwitchMainIdentifierCommand::fromIdentifierCode(self::NEW_CODE);
        $this->getCommandHandler()($command);
    }
}
