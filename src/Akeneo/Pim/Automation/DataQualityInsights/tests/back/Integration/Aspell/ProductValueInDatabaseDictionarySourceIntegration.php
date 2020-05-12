<?php
declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\ProductValueInDatabaseDictionarySource;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class ProductValueInDatabaseDictionarySourceIntegration extends TestCase
{
    public function test_it_returns_a_dictionary_for_en_locales()
    {
        $this->createValidProducts();

        $dictionarySource = $this->getDictionarySource();

        $enLocales = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $dictionary = $dictionarySource->getDictionary($enLocales);

        $this->assertEquals(
            [
                'wonderful' => 'wonderful',
                'text' => 'text',
                'were' => 'were',
                'Gucci' => 'Gucci',
                'Versace' => 'Versace',
                'Louis' => 'Louis',
                'Vuitton' => 'Vuitton',
                'words' => 'words',
                'present' => 'present',
                'several' => 'several',
                'times' => 'times',
            ],
            iterator_to_array($dictionary->getIterator())
        );
    }

    public function test_it_returns_a_dictionary_for_fr_locale()
    {
        $this->createValidProducts();

        $dictionarySource = $this->getDictionarySource();

        $frLocale = new LocaleCollection([
            new LocaleCode('fr_FR'),
        ]);

        $dictionary = $dictionarySource->getDictionary($frLocale);

        $this->assertEquals(
            [
                'Gucci' => 'Gucci',
                'Versace' => 'Versace',
                'Vuitton' => 'Vuitton',
            ],
            iterator_to_array($dictionary->getIterator())
        );
    }

    public function test_it_returns_an_empty_dictionary_for_es_locale()
    {
        $this->createValidProducts();

        $dictionarySource = $this->getDictionarySource();

        $esLocale = new LocaleCollection([
            new LocaleCode('es_ES'),
        ]);

        $dictionary = $dictionarySource->getDictionary($esLocale);

        $this->assertEquals(
            [],
            iterator_to_array($dictionary->getIterator())
        );
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

    public function test_it_return_a_dictionary_that_exclude_several_patterns()
    {
        $this->createProductsWithExcludedPattern();

        $dictionarySource = $this->getDictionarySource();

        $enLocales = new LocaleCollection([
            new LocaleCode('en_US'),
            new LocaleCode('en_GB'),
        ]);

        $dictionary = $dictionarySource->getDictionary($enLocales);

        $this->assertEquals(
            [
                'aValidWord' => 'aValidWord',
            ],
            iterator_to_array($dictionary->getIterator())
        );
    }

    private function createProductsWithExcludedPattern(): void
    {
        $sentence = 'a aa a1aa 1aaa aaa1 http://a.a.a 1.0 1,0 1 aValidWord.';

        $data = [
            'values' => [
                'text_not_localizable' => [
                    ['scope' => null, 'locale' => null, 'data' => $sentence]
                ],
                'text_localizable' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => $sentence],
                    ['scope' => null, 'locale' => 'en_GB', 'data' => $sentence],
                ],
                'textarea_localizable' => [
                    ['scope' => null, 'locale' => 'en_US', 'data' => $sentence],
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

    private function getDictionarySource(): ProductValueInDatabaseDictionarySource
    {
        return $this->get(ProductValueInDatabaseDictionarySource::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
