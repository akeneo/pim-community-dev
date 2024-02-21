<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class CheckAttributeIsNotAFamilyVariantAxisOnDeletionSubscriberIntegration extends TestCase
{
    public function test_it_throws_an_exception_when_the_attribute_is_used_as_variant_axis(): void
    {
        $this->givenAttribute([
            'code' => 'yes_no',
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);

        $this->givenFamily([
            'code' => 'accessories',
            'attributes' => [
                'sku',
                'yes_no',
            ],
        ]);

        $this->givenFamilyVariant([
            'code' => 'attribute_at_variant_level',
            'family' => 'accessories',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['yes_no'],
                    'attributes' => [
                        'sku',
                        'yes_no',
                    ],
                ],
            ],
        ]);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('yes_no');
        $this->assertNotNull($attribute);

        $this->expectException(CannotRemoveAttributeException::class);
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    private function givenAttribute(array $attributeData): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);

        $constraintViolations = $this->get('validator')->validate($attribute);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function givenFamily(array $familyData): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $familyData);

        $constraintViolations = $this->get('validator')->validate($family);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function givenFamilyVariant(array $data): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $constraintViolations = $this->get('validator')->validate($familyVariant);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
