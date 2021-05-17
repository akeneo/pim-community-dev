<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\TableAttribute\Validation;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class TableAttributeValidationIntegration extends TestCase
{
    /** @test */
    public function checking_a_table_attribute_is_properly_configured()
    {
        $attribute = $this->createAttribute(['code' => 'AQR']);
        $this->assertValidationErrors($attribute, ['TODO error message']);
    }

    /** @test */
    public function checking_a_non_table_attribute_has_no_configuration()
    {
        $attribute = $this->createAttribute(
            [
                'code' => 'name',
                'type' => 'pim_catalog_text',
                'table_configuration' => [
                    ['type' => 'text', 'code' => 'ingredients'],
                ],
            ]
        );
        $this->assertValidationErrors($attribute, ['Not relevant']);
    }

    /** @test */
    public function checking_table_attribute_configuration_has_at_least_two_columns()
    {
        $attribute = $this->createAttribute([
            'code' => 'AQR',
            'table_configuration' => [
                ['type' => 'text', 'code' => 'ingredients'],
            ],
        ]);
        $this->assertValidationErrors($attribute, ['TODO e']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createAttribute(array $data): AttributeInterface
    {
        $defaults = ['type' => AttributeTypes::TABLE, 'group' => 'attributeGroupA'];
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, \array_merge($defaults, $data));

        return $attribute;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string[] $expectedErrorMessages
     */
    private function assertValidationErrors(AttributeInterface $attribute, array $expectedErrorMessages): void
    {
        $actualErrorMessages = [];
        foreach ($this->get('validator')->validate($attribute) as $violation) {
            $actualErrorMessages[] = $violation->getMessage();
        }

        foreach ($expectedErrorMessages as $expectedErrorMessage) {
            Assert::assertContains($expectedErrorMessage, $actualErrorMessages);
        }
    }
}
