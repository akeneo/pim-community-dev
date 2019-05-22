<?php


namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\UpdateFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class UpdateFamilyIntegration extends TestCase
{
    /** @var Connection */
    private $dbal;

    /** @var UpdateFamilyInterface */
    private $updateFamilyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->updateFamilyService = $this->get('akeneo.pim.automation.franklin_insights.application.structure.service.update_family');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }

    private function prepare_adds_attribute_to_family()
    {
        /*
        $query = <<<SQL
SELECT family_id, attribute_id FROM akeneo_pim.pim_catalog_family
WHERE family_id = :FAMILY_CODE AND attribute_id = :ATTRIBUTE_CODE
SQL;

        $statement = $this->dbal->executeQuery($query, [
            'FAMILY_CODE' => (string) $familyCode,
            'ATTRIBUTE_CODE' => (string) $attributeCode,
        ]);
        */

    }

    public function test_it_adds_attribute_to_family()
    {
        //$this->prepare_adds_attribute_to_family();

        $familyCode = new FamilyCode('familyA');
        $attributeCode = new AttributeCode('123');

        $this->updateFamilyService->addAttributeToFamily($attributeCode, $familyCode);

        $query = <<<SQL
SELECT family_id, attribute_id FROM akeneo_pim.pim_catalog_family_attribute
WHERE family_id = :FAMILY_CODE AND attribute_id = :ATTRIBUTE_CODE
SQL;

        $statement = $this->dbal->executeQuery($query, [
            'FAMILY_CODE' => (string) $familyCode,
            'ATTRIBUTE_CODE' => (string) $attributeCode,
        ]);

        $result = $statement->fetch();
        $resultCount = $statement->rowCount();

        Assert::assertSame(1, $resultCount);
        Assert::assertSame('123', $result['attribute_id']);
        Assert::assertSame('familyA', $result['family_id']);
    }
}