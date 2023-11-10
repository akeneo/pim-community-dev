<?php

namespace AkeneoTest\Platform\Integration\Installer;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckAttributesCompatibilityWithSaasCommandIntegration extends TestCase
{
    private Connection $dbConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testAllAttributesAreValidOnMinimalCatalog(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('pim:installer:check-attributes');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testWithInvalidAttributes(): void
    {
        $this->insertAttribute('riri', 'vendor_custom_attribute', 'text');
        $this->insertAttribute('fifi', 'pim_catalog_text', 'custom');
        $this->insertAttribute('loulou', 'unknown', 'unknown');

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('pim:installer:check-attributes');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $expectedOutput = 'Checking the attributes table
Invalid attributes were found :
+----------------+-------------------------+--------------+
| Attribute Code | Attribute type          | Backend Type |
+----------------+-------------------------+--------------+
| riri           | vendor_custom_attribute | text         |
| fifi           | pim_catalog_text        | custom       |
| loulou         | unknown                 | unknown      |
+----------------+-------------------------+--------------+
';

        $this->assertEquals($output, $expectedOutput);
    }

    private function insertAttribute(string $code, string $attributeType, string $backendType): void
    {
        $this->dbConnection->executeStatement(
<<<SQL
INSERT INTO pim_catalog_attribute (sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated)
VALUES (0, 1, 1, 1, 1, :code, 'entity', :attributeType, :backendType, NOW(), NOW())
SQL,
            [
                'code' => $code,
                'attributeType' => $attributeType,
                'backendType' => $backendType
            ]
        );
    }
}
