<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierCommand;
use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierHandler;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeDateIntegration extends TestCase
{
    public function testUpdateAttributeUpdateDateOnAttributeOptionEvents()
    {
        $attribute = $this->createSimpleSelectAttributeWithOptions('my_simple_select', []);
        $initialUpdateDate = $this->getAttributeUpdateDate('my_simple_select');

        sleep(1);

        //When I add another one
        $this->createAttributeOption('last_option_code', $attribute, 10001);
        $updateDateAfterAddingOption = $this->getAttributeUpdateDate('my_simple_select');

        Assert::assertNotEquals($initialUpdateDate, $updateDateAfterAddingOption);
    }

    public function testUpdateAttributeUpdateDateOnMainIdentifierUpdate()
    {
        $initialMainIdentifier = $this->getAttributeRepository()->getMainIdentifier();
        Assert::assertTrue($initialMainIdentifier->isMainIdentifier());
        $initialMainIdentifierUpdateDate = $this->getAttributeUpdateDate($initialMainIdentifier->getCode());

        $eanAttribute = $this->createAttribute('ean', [
            'type' => AttributeTypes::IDENTIFIER,
            'useable_as_grid_filter' => true,
        ]);
        Assert::assertFalse($eanAttribute->isMainIdentifier());
        $initialUpdateDate = $this->getAttributeUpdateDate('ean');

        sleep(1);

        //When I update the main identifier
        $command = SwitchMainIdentifierCommand::fromIdentifierCode('ean');
        ($this->getSwithMainCommandHandler())($command);

        $this->get('doctrine.orm.entity_manager')->refresh($initialMainIdentifier);
        Assert::assertFalse($initialMainIdentifier->isMainIdentifier());
        Assert::assertNotEquals(
            $initialMainIdentifierUpdateDate,
            $this->getAttributeUpdateDate($initialMainIdentifier->getCode())
        );

        $this->get('doctrine.orm.entity_manager')->refresh($eanAttribute);
        Assert::assertTrue($eanAttribute->isMainIdentifier());
        Assert::assertNotEquals($initialUpdateDate, $this->getAttributeUpdateDate('ean'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createSimpleSelectAttributeWithOptions(): AttributeInterface
    {
        $attribute = $this->createAttribute('my_simple_select', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $options = [];
        for ($i = 1; $i <= 10000; $i++) {
            $option = new AttributeOption();
            $option->setAttribute($attribute);
            $option->setCode('option' . $i);
            $option->setSortOrder($i);
            $options[] = $option;
        }
        $this->get('pim_catalog.saver.attribute_option')->saveAll($options);

        return $attribute;
    }

    private function createAttributeOption(string $optionCode, AttributeInterface $attribute, int $sortOrder): void
    {
        $option = $this->getAttributeOptionFactory()->create();
        $option->setCode($optionCode);
        $option->setAttribute($attribute);
        $option->setSortOrder($sortOrder);
        $this->getAttributeOptionSaver()->save($option);
    }

    private function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $this->getAttributeSaver()->save($attribute);

        return $attribute;
    }

    private function getAttributeOptionFactory(): SimpleFactoryInterface
    {
        return $this->get('pim_catalog.factory.attribute_option');
    }

    private function getAttributeOptionSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.attribute_option');
    }

    private function getAttributeSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.attribute');
    }

    private function getAttributeUpdateDate(string $attributeCode): string
    {
        return $this->getConnection()->fetchOne(
            'SELECT updated from pim_catalog_attribute where code = :attributeCode;',
            ['attributeCode' => $attributeCode],
        );
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getSwithMainCommandHandler(): SwitchMainIdentifierHandler
    {
        return $this->get(SwitchMainIdentifierHandler::class);
    }

    private function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->get('pim_catalog.repository.attribute');
    }
}
