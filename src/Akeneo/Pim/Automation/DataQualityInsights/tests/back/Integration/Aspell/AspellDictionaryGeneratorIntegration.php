<?php
declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\ProductValueInDatabaseDictionarySource;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class AspellDictionaryGeneratorIntegration extends TestCase
{
    public function test_it_generates_dictionaries_from_product_values_to_filesystem()
    {
        $this->createValidProducts();

        $dictionarySource = $this->get(ProductValueInDatabaseDictionarySource::class);

        $this->get(AspellDictionaryGenerator::class)
            ->generate($dictionarySource);

        $fs = $this->get('oneup_flysystem.mount_manager')
            ->getFilesystem('dataQualityInsightsSharedAdapter');

        $this->assertTrue($fs->has('consistency/text_checker/aspell/custom-dictionary-en.pws'));

        /**
         * Only en_US catalog locale is activated in the minimal catalog so we only expected the 3 following words
         */
        $expected = <<<DICTIONARY
personal_ws-1.1 en 3
Gucci
Versace
Vuitton

DICTIONARY;

        $actual = $fs->read('consistency/text_checker/aspell/custom-dictionary-en.pws');
        $this->assertSame($expected, $actual);

        $this->assertFalse($fs->has('consistency/text_checker/aspell/custom-dictionary-fr.pws'));
        $this->assertFalse($fs->has('consistency/text_checker/aspell/custom-dictionary-es.pws'));
    }

    private function createValidProducts(): void
    {
        $data = [
            'values' => [
                'text_not_localizable' => [
                    ['scope' => null, 'locale' => null, 'data' => 'a wonderful text were Gucci, Versace, and Louis Vuitton words are present several times. Gucci, Versace, Vuitton.']
                ],
                'text_localizable' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => 'a wonderful text were Gucci, Versace, and Louis Vuitton words are present several times.'],
                    ['scope' => null, 'locale' => 'en_GB', 'data' => 'a wonderful text were Gucci, Versace, and Louis Vuitton words are present several times.'],
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => 'un super texte ou les mots Gucci, Versace, et Louis Vuitton sont présents plusieurs fois.'],
                ],
                'textarea_localizable' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => 'a wonderful text were Gucci, Versace, and Louis Vuitton words are present several times.'],
                    ['scope' => null, 'locale' => 'fr_FR', 'data' => 'un super texte ou les mots Gucci, Versace, et Louis Vuitton sont présents plusieurs fois.'],
                    ['scope' => null, 'locale' => 'es_ES', 'data' => 'Olà que tal ? Bien y tu. Una Cerveza por favor.'],
                ],
            ]
        ];

        $this->createProducts($data);
    }

    private function createProducts(array $data)
    {
        $familyCode = $this->createFamily();

        $products = ['product_A', 'product_B', 'product_C'];

        foreach ($products as $productIdentifier) {
            $product = $this->get('akeneo_integration_tests.catalog.product.builder')
                ->withIdentifier($productIdentifier)
                ->withFamily($familyCode)
                ->build();

            $this->get('pim_catalog.updater.product')->update($product, $data);
            $this->get('pim_catalog.saver.product')->save($product);
        }
    }

    private function createFamily(): string
    {
        $this->createAttributeTypeText();
        $this->createAttributeTypeTextLocalizable();
        $this->createAttributeTypeTextareaLocalizable();

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => 'family',
                'attributes' => ['sku', 'text_not_localizable', 'text_localizable', 'textarea_localizable'],
            ]);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family->getCode();
    }

    private function createAttributeTypeText(): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'text_not_localizable',
            'type' => AttributeTypes::TEXT,
            'unique' => false,
            'group' => 'other',
            'localizable' => false
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeTypeTextLocalizable(): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'text_localizable',
            'type' => AttributeTypes::TEXT,
            'unique' => false,
            'group' => 'other',
            'localizable' => true
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeTypeTextareaLocalizable(): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'textarea_localizable',
            'type' => AttributeTypes::TEXTAREA,
            'unique' => false,
            'group' => 'other',
            'localizable' => true
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    protected function tearDown(): void
    {
        $this->ensureDictionariesAreRemoved();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDictionariesAreRemoved();
    }

    private function ensureDictionariesAreRemoved()
    {
        $fs = $this->get('oneup_flysystem.mount_manager')
            ->getFilesystem('dataQualityInsightsSharedAdapter');

        $files = $fs->listContents('consistency/text_checker/aspell');

        foreach ($files as $file) {
            $fs->delete($file['path']);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
