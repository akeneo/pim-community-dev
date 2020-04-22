<?php

declare (strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as PimAttribute;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

final class SelectExactMatchAttributeCodeQueryIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var SelectExactMatchAttributeCodeQueryInterface */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->get('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->get('pim_catalog.saver.attribute');
        $this->query = $this->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_exact_match_attribute_codes');
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code()
    {
        $this->createAttribute('weight', ['en_US' => 'Attribute weight']);
        $this->createAttribute('size', ['en_US' => 'Attribute size']);
        $this->createFamily('a_family', ['weight', 'size']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight', 'size']);
        $this->assertSame(
            [
                'weight' => 'weight',
                'size' => 'size'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_label()
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'size']);
        $this->createFamily('a_family', ['attribute_weight', 'attribute_size']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight', 'size']);
        $this->assertSame(
            [
                'weight' => 'attribute_weight',
                'size' => 'attribute_size'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code_and_label_mix()
    {
        $this->createAttribute('weight_code', ['en_US' => 'weight_label']);
        $this->createAttribute('size_code', ['en_US' => 'Attribute size']);
        $this->createFamily('a_family', ['weight_code', 'size_code']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight_label', 'size_code']);
        $this->assertSame(
            [
                'weight_label' => 'weight_code',
                'size_code' => 'size_code'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_pim_attribute_code_case_insensitive_exact_match_on_code_and_label()
    {
        $this->createAttribute('weight_code', ['en_US' => 'weight_label']);
        $this->createAttribute('size_code', ['en_US' => 'Attribute size']);
        $this->createFamily('a_family', ['weight_code', 'size_code']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['WeiGhT_LaBEl', 'SizE_CodE']);

        $this->assertSame(
            [
                'WeiGhT_LaBEl' => 'weight_code',
                'SizE_CodE' => 'size_code'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_label_with_multiple_english_locales()
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'product weight', 'en_UK' => 'weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'size', 'en_UK' => 'attribute size']);
        $this->createFamily('a_family', ['attribute_weight', 'attribute_size']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight', 'size']);
        $this->assertSame(
            [
                'weight' => 'attribute_weight',
                'size' => 'attribute_size'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_pim_attribute_code_on_attribute_label_with_invalid_codes()
    {
        $this->createAttribute('attribute__weight_', ['en_US' => 'weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'size',]);
        $this->createFamily('a_family', ['attribute__weight_', 'attribute_size']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['attribute (weight)', 'size']);
        $this->assertSame(
            [
                'attribute (weight)' => 'attribute__weight_',
                'size' => 'attribute_size'
            ],
            $attributeCodes
        );
    }

    public function test_it_returns_no_attribute_code()
    {
        $this->createAttribute('attribute_weight', ['en_US' => 'attribute weight']);
        $this->createAttribute('attribute_size', ['en_US' => 'attribute size']);
        $this->createFamily('a_family', ['attribute_weight', 'attribute_size']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight', 'size']);
        $this->assertContainsOnly('null', $attributeCodes);
    }

    public function test_it_handles_attribute_with_no_labels()
    {
        $this->createAttribute('weight_code', ['en_US' => 'weight']);
        $this->createAttribute('weight', []);
        $this->createFamily('a_family', ['weight_code', 'weight']);

        $attributeCodes = $this->query->execute(new FamilyCode('a_family'), ['weight label', 'weight']);
        $this->assertSame(
            [
                'weight label' => null,
                'weight' => 'weight_code',
            ],
            $attributeCodes
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $attributeCode, array $labels): PimAttribute
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => $labels,
            ]
        );

        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => array_merge(['sku'], $attributeCodes),
        ];

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->get('validator')->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
