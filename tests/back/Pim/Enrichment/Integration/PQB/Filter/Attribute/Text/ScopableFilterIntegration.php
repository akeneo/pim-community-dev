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
class ScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_scopable_text',
            'type'                => AttributeTypes::TEXT,
            'localizable'         => false,
            'scopable'            => true,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_scopable_text']
        ]);

        $this->createProduct('cat', [
            new SetFamily('a_family'),
            new SetTextValue('a_scopable_text', 'ecommerce', null, 'black cat'),
            new SetTextValue('a_scopable_text', 'tablet', null, 'cat'),
        ]);

        $this->createProduct('cattle', [
            new SetFamily('a_family'),
            new SetTextValue('a_scopable_text', 'ecommerce', null, 'cattle'),
            new SetTextValue('a_scopable_text', 'tablet', null, 'cattle'),
        ]);

        $this->createProduct('dog', [
            new SetFamily('a_family'),
            new SetTextValue('a_scopable_text', 'ecommerce', null, 'just a dog...'),
            new SetTextValue('a_scopable_text', 'tablet', null, 'dog'),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_text', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_scopable_text', Operators::STARTS_WITH, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_scopable_text', Operators::CONTAINS, 'nope', ['scope' => 'tablet']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_scopable_text', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'ecommerce']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_scopable_text', Operators::EQUALS, 'cat', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::IS_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_scopable_text', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testErrorScopable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_text" expects a scope, none given.');

        $this->executeFilter([['a_scopable_text', Operators::NOT_EQUAL, 'data']]);
    }

    public function testScopeNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_scopable_text" expects an existing scope, "NOT_FOUND" given.');

        $this->executeFilter([['a_scopable_text', Operators::NOT_EQUAL, 'text', ['scope' => 'NOT_FOUND']]]);
    }
}
