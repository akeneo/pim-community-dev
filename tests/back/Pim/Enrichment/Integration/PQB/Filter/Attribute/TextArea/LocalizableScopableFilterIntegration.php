<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\TextArea;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateLocaleForChannel('fr_Fr', 'ecommerce');

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_scopable_text_area']
        ]);

        $this->createProduct('cat', [
            new SetFamily('a_family'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'en_US', 'black cat'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'en_US', 'cat'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'fr_FR', 'chat noir'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'fr_FR', 'chat'),
        ]);

        $this->createProduct('cattle', [
            new SetFamily('a_family'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'en_US', 'cattle'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'en_US', 'cattle'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'fr_FR', 'bétail'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'fr_FR', 'bétail'),
        ]);

        $this->createProduct('dog', [
            new SetFamily('a_family'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'en_US', 'just a dog...'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'en_US', 'dog'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'fr_FR', 'juste un chien...'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet', 'fr_FR', 'chien'),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::CONTAINS, 'nope', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_text_area" expects a locale, none given.');

        $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'data']]);
    }
}
