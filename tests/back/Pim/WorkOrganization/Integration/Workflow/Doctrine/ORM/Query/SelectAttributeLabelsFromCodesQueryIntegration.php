<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as PimAttribute;
use Akeneo\Test\Integration\TestCase;

class SelectAttributeLabelsFromCodesQueryIntegration extends TestCase
{
    private $attributeBuilder;

    private $attributeSaver;

    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->query = $this->getFromTestContainer('pimee_workflow.query.select_attribute_labels_from_codes');
    }

    /**
     * @test
     */
    public function it_returns_attribute_labels_from_codes()
    {
        $this->createAttribute('name', ['en_US' => 'name', 'fr_FR' => 'nom']);
        $this->createAttribute('weight', ['en_US' => 'weight']);
        $this->createAttribute('description', []);

        $labels = $this->query->execute(['name', 'weight', 'description']);
        $expectedLabels = [
            'name' => ['en_US' => 'name', 'fr_FR' => 'nom'],
            'weight' => ['en_US' => 'weight'],
            'description' => [],
        ];

        $this->assertEqualsCanonicalizing($expectedLabels, $labels);
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

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
