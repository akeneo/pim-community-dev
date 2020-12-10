<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Structure\Integration\Validation;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use AkeneoTest\Pim\Structure\Integration\Attribute\Validation\AbstractAttributeTestCase;
use PHPUnit\Framework\Assert;

class AssetCollectionIntegration extends AbstractAttributeTestCase
{
    /**
     * @test
     */
    public function asset_family_should_be_defined(): void
    {
        $attribute = $this->createAndUpdateAttribute(
            [
                'code' => 'packshots',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'attributeGroupA',
            ]
        );
        $this->assertViolationMessage(
            $attribute,
            'reference_data_name',
            'You need to define an asset family for your attribute'
        );
    }

    /**
     * @test
     */
    public function asset_family_identifier_should_be_valid(): void
    {
        $attribute = $this->createAndUpdateAttribute(
            [
                'code' => 'packshots',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'attributeGroupA',
                'reference_data_name' => '123/invalid',
            ]
        );
        $this->assertViolationMessage(
            $attribute,
            'reference_data_name',
            'The asset family "123/invalid" identifier is not valid'
        );
    }

    /**
     * @test
     */
    public function reference_entity_should_exist_for_reference_entity_single_link(): void
    {
        $attribute = $this->createAndUpdateAttribute(
            [
                'code' => 'packshots',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'attributeGroupA',
                'reference_data_name' => 'unknown',
            ]
        );
        $this->assertViolationMessage(
            $attribute,
            'reference_data_name',
            'The asset family "unknown" does not exist.'
        );
    }

    /**
     * @test
     */
    public function asset_collection_should_not_have_a_default_value(): void
    {
        $attribute = $this->createAndUpdateAttribute(
            [
                'code' => 'packshots',
                'type' => 'pim_catalog_asset_collection',
                'group' => 'attributeGroupA',
                'reference_data_name' => 'packshot',
                'default_value' => true,
            ]
        );
        $this->assertViolationMessage(
            $attribute,
            'default_value',
            'This attribute type cannot have a default value.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $handler = $this->get('akeneo_assetmanager.application.asset_family.create_asset_family_handler');
        $handler(new CreateAssetFamilyCommand('packshot', [], [], [], []));
    }

    private function createAndUpdateAttribute(array $data): AttributeInterface
    {
        $attribute = $this->createAttribute();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        return $attribute;
    }

    private function assertViolationMessage(AttributeInterface $attribute, string $propertyPath, string $message): void
    {
        $violations = $this->validateAttribute($attribute);
        Assert::assertCount(1, $violations);
        Assert::assertSame($propertyPath, $violations->get(0)->getPropertyPath());
        Assert::assertSame($message, $violations->get(0)->getMessage());
    }
}
