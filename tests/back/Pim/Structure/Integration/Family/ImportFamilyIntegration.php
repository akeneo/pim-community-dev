<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductUpdater;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Box\Spout\Writer\WriterFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ImportFamilyIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_footwear_family_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_footwear_family_import';

    private JobLauncher $jobLauncher;
    private FamilyRepositoryInterface $familyRepository;
    private ProductRepositoryInterface $productRepository;
    private ProductBuilderInterface $productBuilder;
    private ProductUpdater $productUpdater;
    private ValidatorInterface $productValidator;
    private ProductSaver $productSaver;
    private GetProductCompletenesses $getProductCompletenesses;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->familyRepository = $this->get('pim_catalog.repository.family');
        $this->productRepository = $this->get('pim_catalog.repository.product');
        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');
        $this->productValidator = $this->get('pim_catalog.validator.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->getProductCompletenesses = $this->get('akeneo.pim.enrichment.product.query.get_product_completenesses');
    }

    public function test_it_updates_a_family_and_creates_a_new_one_in_csv(): void
    {
        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('[heels]', $heelsFamily->getLabel());
        self::assertSame('[name]', $heelsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('color,heel_color,name,price,size,sku,sole_color', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));
        self::assertSame('color,description,heel_color,name,price,side_view,size,sku,sole_color', $this->getRequirementAttributeCodes($heelsFamily, 'tablet'));
        self::assertNull($this->getFamily('tractors'));

        $content = <<<CSV
        code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
        heels;sku,name,manufacturer,heel_color;sku;manufacturer;manufacturer,heel_color;Heels
        tractors;sku,name,manufacturer;name;manufacturer;;Tractor
        CSV;
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $content);

        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('[heels]', $heelsFamily->getLabel());
        self::assertSame('[sku]', $heelsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('manufacturer,sku', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));
        self::assertSame('heel_color,manufacturer,sku', $this->getRequirementAttributeCodes($heelsFamily, 'tablet'));

        $tractorsFamily = $this->getFamily('tractors');
        self::assertNotNull($tractorsFamily);
        self::assertSame('[tractors]', $tractorsFamily->getLabel());
        self::assertSame('[name]', $tractorsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('manufacturer,sku', $this->getRequirementAttributeCodes($tractorsFamily, 'mobile'));
        self::assertSame('sku', $this->getRequirementAttributeCodes($tractorsFamily, 'tablet'));
    }

    public function test_it_updates_a_family_and_creates_a_new_one_in_xlsx(): void
    {
        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('[heels]', $heelsFamily->getLabel());
        self::assertSame('[name]', $heelsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('color,heel_color,name,price,size,sku,sole_color', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));
        self::assertSame('color,description,heel_color,name,price,side_view,size,sku,sole_color', $this->getRequirementAttributeCodes($heelsFamily, 'tablet'));
        self::assertNull($this->getFamily('tractors'));

        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_family_import');
        $writer = WriterFactory::create('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows([
            ['code', 'attributes', 'attribute_as_label', 'requirements-mobile', 'requirements-tablet', 'label-en_US'],
            ['heels', 'sku,name,manufacturer,heel_color', 'sku', 'manufacturer', 'manufacturer,heel_color', 'Heels'],
            ['tractors', 'sku,name,manufacturer', 'name', 'manufacturer', '', 'Tractor'],
        ]);
        $writer->close();
        $this->jobLauncher->launchImport(static::XLSX_IMPORT_JOB_CODE, file_get_contents($temporaryFile), null, [], [], 'xlsx');

        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('[heels]', $heelsFamily->getLabel());
        self::assertSame('[sku]', $heelsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('manufacturer,sku', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));
        self::assertSame('heel_color,manufacturer,sku', $this->getRequirementAttributeCodes($heelsFamily, 'tablet'));

        $tractorsFamily = $this->getFamily('tractors');
        self::assertNotNull($tractorsFamily);
        self::assertSame('[tractors]', $tractorsFamily->getLabel());
        self::assertSame('[name]', $tractorsFamily->getAttributeAsLabel()->getLabel());
        self::assertSame('manufacturer,sku', $this->getRequirementAttributeCodes($tractorsFamily, 'mobile'));
        self::assertSame('sku', $this->getRequirementAttributeCodes($tractorsFamily, 'tablet'));
    }

    public function test_it_updates_product_when_its_family_is_updated(): void
    {
        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('color,heel_color,name,price,size,sku,sole_color', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));

        // for mobile color,heel_color,name,price,size,sku,sole_color
        $product = $this->productBuilder->createProduct('test1', 'heels');
        $this->productUpdater->update($product, ['values' => [
            'name' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => 'test1',
                ],
            ],
            'color' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'red',
                ],
            ],
        ]]);
        self::assertSame(0, $this->productValidator->validate($product)->count());
        $this->productSaver->save($product);

        $productCompletenessCollection = $this->getProductCompletenesses->fromProductId(
            $this->productRepository->findOneByIdentifier('test1')->getId()
        );
        $productCompleteness = $productCompletenessCollection->getCompletenessForChannelAndLocale('mobile', 'en_US');
        self::assertNotNull($productCompleteness);
        self::assertSame(7, $productCompleteness->requiredCount());
        self::assertSame(4, $productCompleteness->missingCount());
        self::assertSame(42, $productCompleteness->ratio());
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('akeneo.pim.structure.query.get_required_attributes_masks')->clearCache();

        // Remove color from the list of requirements for mobile scope.
        $content = <<<CSV
        code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
        heels;sku,name,manufacturer,heel_color;sku;manufacturer,name;heel_color;Heels
        CSV;
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $content);

        $heelsFamily = $this->getFamily('heels');
        self::assertNotNull($heelsFamily);
        self::assertSame('manufacturer,name,sku', $this->getRequirementAttributeCodes($heelsFamily, 'mobile'));

        $this->get('akeneo.pim.structure.query.get_required_attributes_masks')->clearCache();
        $productCompletenessCollection = $this->getProductCompletenesses->fromProductId(
            $this->productRepository->findOneByIdentifier('test1')->getId()
        );
        $productCompleteness = $productCompletenessCollection->getCompletenessForChannelAndLocale('mobile', 'en_US');
        self::assertNotNull($productCompleteness);
        self::assertSame(3, $productCompleteness->requiredCount());
        self::assertSame(1, $productCompleteness->missingCount());
        self::assertSame(66, $productCompleteness->ratio());
    }

    private function getFamily(string $familycode): ?FamilyInterface
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        return $this->familyRepository->findOneByIdentifier($familycode);
    }

    private function getRequirementAttributeCodes(FamilyInterface $heelsFamily, string $channel): string
    {
        $attributeCodes = array_filter(array_map(
            fn (AttributeRequirementInterface $attributeRequirement) => $attributeRequirement->getChannel()->getCode() === $channel
                ? $attributeRequirement->getAttributeCode()
                : null,
            $heelsFamily->getAttributeRequirements()
        ));
        sort($attributeCodes);

        return implode(',', $attributeCodes);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }
}
