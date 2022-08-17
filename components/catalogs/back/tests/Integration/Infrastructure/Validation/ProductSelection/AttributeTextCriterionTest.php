<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Infrastructure\Validation\CatalogUpdatePayload;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion\AttributeTextCriterionStructure;
use Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion\AttributeTextCriterionValues;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductSelection\AttributeTextCriterion\
 */
class AttributeTextCriterionTest extends IntegrationTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    /**
     * @dataProvider validCriterionDataProvider
     */
    public function testItValidates(array $attribute, array $criterion): void
    {
        $this->createAttribute($attribute);

        $violations = $this->validator->validate(
            $criterion,
            new Assert\Sequentially([
                new AttributeTextCriterionStructure(),
                new AttributeTextCriterionValues(),
            ])
        );

        $this->assertEmpty($violations);
    }

    public function validCriterionDataProvider(): array
    {
        return [
            'localizable and scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ],
            'scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => true,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ]
            ],
            'localizable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => true,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => 'en_US',
                ]
            ],
            'non localizable and non scopable attribute' => [
                'attribute' => [
                    'code' => 'name',
                    'type' => 'pim_catalog_text',
                    'group' => 'other',
                    'scopable' => false,
                    'localizable' => false,
                ],
                'criterion' => [
                    'field' => 'name',
                    'operator' => '=',
                    'value' => 'blue',
                    'scope' => null,
                    'locale' => null,
                ]
            ],
        ];
    }
}
