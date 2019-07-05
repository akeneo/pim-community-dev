<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectExactMatchAttributeCodesFromOtherFamiliesQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as PimAttribute;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface as PimFamily;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SelectExactMatchAttributeCodesFromOtherFamiliesQueryIntegration extends TestCase
{
    /** @var SelectExactMatchAttributeCodesFromOtherFamiliesQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_exact_match_attribute_code_from_other_family_query');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $attributeCode, array $labels): PimAttribute
    {
        $attribute = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')
            ->build([
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => $labels,
            ]);

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createFamily(string $familyCode, array $attributeCodes): PimFamily
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $familyCode,
                'attributes' => array_merge(['sku'], $attributeCodes),
            ]);

        $this->getFromTestContainer('validator')->validate($family);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);

        return $family;
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code(): void
    {
        $this->createAttribute('weight', ['en_US' => 'Attribute weight']);
        $this->createAttribute('size', ['en_US' => 'Attribute size']);
        $this->createFamily('family_a', ['weight']);
        $this->createFamily('family_b', ['weight', 'size']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'Weight', 'Size'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'Weight' => 'weight',
            'Size' => 'size',
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_label(): void
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'Weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'Size']);
        $this->createFamily('family_a', ['attribute_weight']);
        $this->createFamily('family_b', ['attribute_weight', 'attribute_size']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'Weight', 'Size'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'Weight' => 'attribute_weight',
            'Size' => 'attribute_size',
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code_and_label_mix(): void
    {
        $this->createAttribute('weight_code', ['en_US' => 'weight_label']);
        $this->createAttribute('size_code', ['en_US' => 'Attribute size']);
        $this->createFamily('family_a', ['weight_code']);
        $this->createFamily('family_b', ['weight_code', 'size_code']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'weight_label', 'size_code'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'weight_label' => 'weight_code',
            'size_code' => 'size_code',
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

    public function test_it_returns_pim_attribute_code_case_insensitive_exact_match_on_code_and_label(): void
    {
        $this->createAttribute('weight_code', ['en_US' => 'weight_label']);
        $this->createAttribute('size_code', ['en_US' => 'Attribute size']);
        $this->createFamily('family_a', ['weight_code']);
        $this->createFamily('family_b', ['weight_code', 'size_code']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'WeiGhT_LaBEl', 'SizE_CodE'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'WeiGhT_LaBEl' => 'weight_code',
            'SizE_CodE' => 'size_code',
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_label_with_multiple_english_locales(): void
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'product weight', 'en_UK' => 'weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'size', 'en_UK' => 'attribute size']);
        $this->createFamily('family_a', ['attribute_weight']);
        $this->createFamily('family_b', ['attribute_weight', 'attribute_size']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'Weight', 'Size'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'Weight' => 'attribute_weight',
            'Size' => 'attribute_size',
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

    public function test_it_returns_no_attribute_code()
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'attribute weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'attribute size']);
        $this->createFamily('family_a', ['attribute_weight']);
        $this->createFamily('family_b', ['attribute_weight', 'attribute_size']);

        $familyCode = new FamilyCode('family_a');
        $pendingAttributesFranklinLabels = ['Color', 'Weight', 'Size'];
        $expectedPimAttributeCodeMatches = [
            'Color' => null,
            'Weight' => null,
            'Size' => null,
        ];

        $matchedAttributeCodes = $this->query->execute($familyCode, $pendingAttributesFranklinLabels);

        $this->assertSame($expectedPimAttributeCodeMatches, $matchedAttributeCodes);
    }

}
