<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductGrid;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelImagesFromCodesIntegration extends TestCase
{
    /** @test */
    public function it_fetches_images_from_product_model_codes_with_numeric_attribute_codes(): void
    {
        $this->createAttribute([
            'code' => '123',
            'type' => AttributeTypes::IMAGE,
            'scopable' => false,
            'localizable' => false,
            'group' => 'other',
        ]);
        $this->createAttribute([
            'code' => 'yesno',
            'type' => AttributeTypes::BOOLEAN,
            'scopable' => false,
            'localizable' => false,
            'group' => 'other',
        ]);
        $this->createFamily([
            'code' => 'familyA',
            'attributes' => ['sku', 'yesno', '123'],
            'attribute_as_image' => '123',
        ]);
        $this->createFamilyVariant([
            'code' => 'variant',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'attributes' => ['sku', '123'],
                    'axes' => ['yesno'],
                ],
            ],
        ]);
        $this->createProductModel([
            'code' => 'parent',
            'family_variant' => 'variant',
        ]);
        $this->createProduct('child', [
            new SetFamily('familyA'),
            new ChangeParent('parent'),
            new SetBooleanValue('yesno', null, null, true),
            new SetImageValue('123', null, null, $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
        ]);

        $productModelImagesFromCodes = $this->get('akeneo.pim.enrichment.product.grid.query.product_model_images_from_codes');
        $images = $productModelImagesFromCodes(['parent'], 'ecommerce', 'en_US');

        Assert::assertIsArray($images);
        Assert::assertCount(1, $images);
        Assert::assertArrayHasKey('parent', $images);
        Assert::assertIsArray($images['parent']);
        Assert::assertCount(1, $images['parent']);
        Assert::assertArrayHasKey('image', $images['parent']);
        Assert::assertInstanceOf(MediaValueInterface::class, $images['parent']['image']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(array $data): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(
            0,
            $violations,
            \sprintf('The attribute is invalid: %s', (string)$violations)
        );
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $violations = $this->get('validator')->validate($family);
        Assert::assertCount(
            0,
            $violations,
            \sprintf('The family is invalid: %s', (string)$violations)
        );
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(array $data): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $violations = $this->get('validator')->validate($familyVariant);
        Assert::assertCount(
            0,
            $violations,
            \sprintf('The family variant is invalid: %s', (string)$violations)
        );
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    private function createProductModel(array $data): void
    {
        $model = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($model, $data);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($model);
        Assert::assertCount(
            0,
            $violations,
            \sprintf('The product model is invalid: %s', (string)$violations)
        );
        $this->get('pim_catalog.saver.product_model')->save($model);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }
}
