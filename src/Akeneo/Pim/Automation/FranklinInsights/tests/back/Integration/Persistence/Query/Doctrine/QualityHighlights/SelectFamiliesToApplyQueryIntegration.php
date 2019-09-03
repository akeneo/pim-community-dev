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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SelectFamiliesToApplyQueryIntegration extends TestCase
{
    /** @var ValidatorInterface */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->getFromTestContainer('validator');
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code()
    {
        $attributeCode1 = $this->createAttribute('weight', ['en_US' => 'Weight']);
        $attributeCode2 = $this->createAttribute('color', ['en_US' => 'Color', 'en_CA' => 'Couleur']);
        $attributeCode3 = $this->createAttribute('size', ['en_US' => 'size']);
        $family1Code = $this->createFamily('headphones', [$attributeCode1, $attributeCode2], ['en_US' => 'Headphones', 'en_CA' => 'Casques audio']);
        $family2Code = $this->createFamily('router', [$attributeCode3], []);

        $families = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_families_to_apply')
            ->execute([$family1Code, $family2Code]);

        $expectedFamilies = [
            [
                'code' => 'headphones',
                'attributes' => ['sku', 'weight', 'color'],
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Headphones',
                    ],
                    [
                        'locale' => 'en_CA',
                        'label' => 'Casques audio',
                    ],
                ],
            ],
            [
                'code' => 'router',
                'attributes' => ['sku', 'size'],
                'labels' => [],
            ],
        ];

        $this->assertEqualsCanonicalizing($expectedFamilies, $families);
    }

    private function createAttribute(string $attributeCode, $labels): string
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => $labels,
            ]
        );
        $this->validator->validate($attribute);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        return $attribute->getCode();
    }

    private function createFamily(string $familyCode, array $attributeCodes, array $labels): string
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $familyCode,
                'attributes' => array_merge(['sku'], $attributeCodes),
                'labels' => $labels
            ]);

        $this->validator->validate($family);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);

        return $family->getCode();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
