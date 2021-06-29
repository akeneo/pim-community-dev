<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use PHPUnit\Framework\Assert;

class FamilyRepositoryIntegration extends TestCase
{
    /** @var SimpleFactoryInterface */
    private $familyFactory;

    /** @var ObjectUpdaterInterface */
    private $familyUpdater;

    /** @var SaverInterface */
    private $familySaver;

    /** @var EntityBuilder */
    private $familyVariantBuilder;

    /** @var SaverInterface */
    private $familyVariantSaver;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var FamilyRepository */
    private $familyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->familyFactory = $this->get('pim_catalog.factory.family');
        $this->familyUpdater = $this->get('pim_catalog.updater.family');
        $this->familySaver = $this->get('pim_catalog.saver.family');
        $this->familyVariantBuilder = $this->get('akeneo_integration_tests.base.family_variant.builder');
        $this->familyVariantSaver = $this->get('pim_catalog.saver.family_variant');
        $this->attributeBuilder = $this->get('akeneo_integration_tests.base.attribute.builder');
        $this->attributeSaver = $this->get('pim_catalog.saver.attribute');
        $this->familyRepository = $this->get('pim_catalog.repository.family');
    }

    public function test_it_finds_results_with_offset_and_limit(): void
    {
        $this->loadFixtures();

        $firstPage = $this->familyRepository->getWithVariants(null, ['page' => '1'], 2);
        Assert::assertCount(2, $firstPage);
        Assert::assertContainsOnlyInstancesOf(FamilyInterface::class, $firstPage);
        Assert::assertSame('jacket', $firstPage[0]->getCode());
        Assert::assertSame('shoes', $firstPage[1]->getCode());

        $secondPage = $this->familyRepository->getWithVariants(null, ['page' => '2'], 2);
        Assert::assertCount(1, $secondPage);
        Assert::assertContainsOnlyInstancesOf(FamilyInterface::class, $secondPage);
        Assert::assertSame('tshirt', $secondPage[0]->getCode());
    }

    public function test_it_is_able_to_search_a_family_on_another_page(): void
    {
        $this->loadFixtures();

        $firstPage = $this->familyRepository->getWithVariants('tshirt', ['page' => '1'], 2);
        Assert::assertCount(1, $firstPage);
        Assert::assertContainsOnlyInstancesOf(FamilyInterface::class, $firstPage);
        Assert::assertSame('tshirt', $firstPage[0]->getCode());
    }

    public function test_it_is_able_to_search_family_by_identifiers(): void
    {
        $this->loadFixtures();

        $results = $this->familyRepository->getWithVariants(null, ['identifiers' => ['tshirt', 'jacket']], 2);
        Assert::assertCount(2, $results);
        Assert::assertContainsOnlyInstancesOf(FamilyInterface::class, $results);
        Assert::assertSame('jacket', $results[0]->getCode());
        Assert::assertSame('tshirt', $results[1]->getCode());
    }

    private function loadFixtures(): void
    {
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
        ]);

        $this->createAttribute([
            'code' => 'size',
            'type' => 'pim_catalog_simpleselect',
            'group' => 'other',
        ]);

        $this->createFamily([
            'code' => 'tshirt',
            'attributes' => [
                'sku',
                'color',
                'size',
            ],
        ]);

        $this->createFamily([
            'code' => 'shoes',
            'attributes' => [
                'sku',
                'color',
                'size',
            ],
        ]);

        $this->createFamily([
            'code' => 'jacket',
            'attributes' => [
                'sku',
                'color',
                'size',
            ],
        ]);

        $this->createFamilyVariant([
            'code' => 'blue_shoes',
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
            'family' => 'shoes',
        ]);

        $this->createFamilyVariant([
            'code' => 'blue_tshirt',
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
            'family' => 'tshirt',
        ]);

        $this->createFamilyVariant([
            'code' => 'blue_jacket',
            'variant_attribute_sets' => [
                [
                    'axes' => ['color'],
                    'attributes' => [],
                    'level' => 1,
                ],
            ],
            'family' => 'jacket',
        ]);
    }

    private function createAttribute(array $data = []): AttributeInterface
    {
        $attribute = $this->attributeBuilder->build($data, true);
        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    private function createFamily($data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createFamilyVariant(array $data = []): FamilyVariantInterface
    {
        $familyVariant = $this->familyVariantBuilder->build($data, true);
        $this->familyVariantSaver->save($familyVariant);

        return $familyVariant;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
