<?php


namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Structure\Service;


use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class AddAttributeToFamilyIntegration extends TestCase
{
    /** @var Connection */
    private $dbal;

    /** @var AddAttributeToFamilyInterface */
    private $updateFamilyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbal = $this->get('database_connection');
        $this->updateFamilyService = $this->get('akeneo.pim.automation.franklin_insights.application.structure.service.add_attribute_to_family');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_adds_attribute_to_family()
    {
        $familyCode = new FamilyCode('familyA');
        $attributeCode = new AttributeCode('a_simple_select_size');

        $beforeStatement = $this->executeGetFamilyAttributeQuery($familyCode, $attributeCode);

        $this->updateFamilyService->addAttributeToFamily($attributeCode, $familyCode);

        $afterStatement = $this->executeGetFamilyAttributeQuery($familyCode, $attributeCode);

        Assert::assertSame(0, $beforeStatement->rowCount());
        Assert::assertSame(1, $afterStatement->rowCount());

        $result = $afterStatement->fetch();
        Assert::assertSame('a_simple_select_size', $result['attribute_code']);
        Assert::assertSame('familyA', $result['family_code']);
    }

    private function executeGetFamilyAttributeQuery(FamilyCode $familyCode, AttributeCode $attributeCode)
    {
        $query = <<<SQL
SELECT family_id, attribute_id, pcf.code AS family_code, pca.code AS attribute_code 
FROM pim_catalog_family_attribute pcfa
JOIN pim_catalog_family pcf ON pcfa.family_id = pcf.id
JOIN pim_catalog_attribute pca on pcfa.attribute_id = pca.id
WHERE pcf.code = :FAMILY_CODE AND pca.code = :ATTRIBUTE_CODE
SQL;

        return $this->dbal->executeQuery($query, [
            'FAMILY_CODE' => (string) $familyCode,
            'ATTRIBUTE_CODE' => (string) $attributeCode,
        ]);
    }
}
