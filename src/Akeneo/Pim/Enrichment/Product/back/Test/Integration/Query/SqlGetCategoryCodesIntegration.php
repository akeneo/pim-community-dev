<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Query;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetCategoryCodesIntegration extends TestCase
{
    private const UUID_FOO = 'eac5393e-8de8-4d3a-90db-93bbba8b4ffb';
    private const UUID_BAR = '49aa038f-b7d9-465c-b12d-88f048d60dd4';
    private const UUID_BAZ = 'a76cbde9-929b-4d2c-8ff1-a69e48cf063d';

    private GetCategoryCodes $getCategoryCodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getCategoryCodes = $this->get('Akeneo\Pim\Enrichment\Product\Domain\Query\GetCategoryCodes');

        $this->createProductModel([
            'code' => 'root_a1',
            'family_variant' => 'familyVariantA1',
            'categories' => ['categoryA'],
        ]);
        $this->createProductModel([
            'code' => 'subpm_a1',
            'family_variant' => 'familyVariantA1',
            'parent' => 'root_a1',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']]
            ],
            'categories' => ['categoryA1'],
        ]);
        $this->createProductModel([
            'code' => 'root_a2',
            'family_variant' => 'familyVariantA2',
            'categories' => ['categoryC'],
        ]);

        $this->upsertProduct(
            Uuid::fromString(self::UUID_FOO),
            [
                new SetIdentifierValue('sku', 'foo'),
                new SetCategories(['categoryA', 'categoryB']),
            ]
        );
        $this->upsertProduct(
            Uuid::fromString(self::UUID_BAR),
            [
                new ChangeParent('subpm_a1'),
                new SetIdentifierValue('sku', 'bar'),
                new SetBooleanValue('a_yes_no', null, null, true),
            ]
        );
        $this->upsertProduct(
            Uuid::fromString(self::UUID_BAZ),
            [
                new ChangeParent('root_a2'),
                new SetIdentifierValue('sku', 'baz'),
                new SetBooleanValue('a_yes_no', null, null, false),
                new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
                new SetCategories(['categoryA', 'categoryA1']),
            ]
        );
    }

    /** @test */
    public function it_gets_product_category_codes_by_uuids(): void
    {
        Assert::assertSame([], $this->getCategoryCodes->fromProductUuids([]));
        $this->assertEqualArrays(
            [
                self::UUID_FOO => ['categoryA', 'categoryB'],
                self::UUID_BAR => ['categoryA', 'categoryA1'],
                self::UUID_BAZ => ['categoryA', 'categoryA1', 'categoryC'],
            ],
            $this->getCategoryCodes->fromProductUuids([
                Uuid::fromString(self::UUID_FOO),
                Uuid::fromString(self::UUID_BAZ),
                Uuid::uuid4(),
                Uuid::fromString(self::UUID_BAR),
                Uuid::fromString(self::UUID_BAZ),
            ])
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function upsertProduct(UuidInterface $uuid, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');

        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->getUserId('admin'),
                ProductUuid::fromUuid($uuid),
                $userIntents
            )
        );
    }

    private function createProductModel(array $data): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        Assert::assertCount(0, $this->get('validator')->validate($productModel));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    private function assertEqualArrays(array $expected, array $actual): void
    {
        Assert::assertSameSize($expected, $actual);
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual);
            Assert::assertEqualsCanonicalizing($value, $actual[$key]);
        }
    }
}
