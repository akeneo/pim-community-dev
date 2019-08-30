<?php


namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class CreateAttributeIntegration extends TestCase
{
    /** @var CreateAttributeInterface */
    private $createAttributeService;
    /** @var Connection */
    private $dbal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->createAttributeService = $this->get('akeneo.pim.automation.franklin_insights.application.structure.service.create_attribute');
    }

    /**
     * @dataProvider provideAttributeCreation
     */
    public function test_it_creates_attribute($code, $label, $type, $expectedCode): void
    {
        $this->createAttributeService->create(
            new Attribute(
                new AttributeCode($code),
                new AttributeLabel($label),
                new AttributeType($type)
            )
        );
        $query = <<<SQL
SELECT code, attribute_type FROM pim_catalog_attribute WHERE code = :code
SQL;
        $statement = $this->dbal->executeQuery($query, [
            'code' => $code
        ]);

        $result = $statement->fetch();

        Assert::assertSame($expectedCode, $result['code']);
        Assert::assertSame($type, $result['attribute_type']);
    }

    public function test_it_creates_an_attribute_when_attribute_code_already_exists(): void
    {
        $attributeCodeText = 'a_simple_select';

        $beforeStatement = $this->executeGetAttributeQuery($attributeCodeText);

        $this->expectException(\Exception::class);

        $this->createAttributeService->create(
            new Attribute(
                new AttributeCode($attributeCodeText),
                new AttributeLabel('A simple select'),
                new AttributeType(AttributeTypes::TEXT)
            )
        );

        $afterStatement = $this->executeGetAttributeQuery($attributeCodeText);

        Assert::assertEquals(1, $beforeStatement->rowCount());
        Assert::assertEquals(1, $afterStatement->rowCount());
    }

    private function executeGetAttributeQuery(string $code)
    {
        $query = <<<SQL
SELECT * FROM pim_catalog_attribute pca
WHERE pca.code = :CODE
SQL;
        return $this->dbal->executeQuery($query, [
            'CODE' => $code
        ]);
    }

    public function provideAttributeCreation(): array
    {
        return [
            ['franklin_code_text', 'franklin_label_text', AttributeTypes::TEXT, 'franklin_code_text'],
            ['franklin_code_number', 'franklin_label_number', AttributeTypes::NUMBER, 'franklin_code_number'],
        ];
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
