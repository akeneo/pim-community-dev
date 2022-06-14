<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Text;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
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
            'code'                => 'a_localizable_scopable_text',
            'type'                => AttributeTypes::TEXT,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_scopable_text']
        ]);

        $this->createProduct('cat', [
            new SetFamily('a_family'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'en_US', 'black cat'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'en_US', 'cat'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'fr_FR', 'chat noir'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'fr_FR', 'chat'),
        ]);

        $this->createProduct('cattle', [
            new SetFamily('a_family'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'en_US', 'cattle'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'en_US', 'cattle'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'fr_FR', 'bétail'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'fr_FR', 'bétail'),
        ]);

        $this->createProduct('dog', [
            new SetFamily('a_family'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'en_US', 'just a dog...'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'en_US', 'dog'),
            new SetTextValue('a_localizable_scopable_text', 'ecommerce', 'fr_FR', 'juste un chien...'),
            new SetTextValue('a_localizable_scopable_text', 'tablet', 'fr_FR', 'chien'),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::STARTS_WITH, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::CONTAINS, 'nope', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::IS_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_scopable_text" expects a locale, none given.');

        $this->executeFilter([['a_localizable_scopable_text', Operators::NOT_EQUAL, 'data']]);
    }
}
