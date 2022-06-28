<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\TextArea;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Text area sorter integration tests for localizable and scopable attribute
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateLocaleForChannel('fr_FR', 'ecommerce');

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createProduct('cat', [
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce', 'en_US', 'black cat'),
            new SetTextareaValue('a_localizable_scopable_text_area','tablet', 'en_US','cat'),
            new SetTextareaValue('a_localizable_scopable_text_area','ecommerce', 'fr_FR','chat noir'),
            new SetTextareaValue('a_localizable_scopable_text_area','tablet', 'fr_FR','chat'),

        ]);

        $this->createProduct('cattle', [
            new SetTextareaValue('a_localizable_scopable_text_area','ecommerce','en_US', 'cattle'),
            new SetTextareaValue('a_localizable_scopable_text_area','tablet','en_US', 'cattle'),
            new SetTextareaValue('a_localizable_scopable_text_area','ecommerce','fr_FR', 'bétail'),
            new SetTextareaValue('a_localizable_scopable_text_area','tablet','fr_FR', 'bétail'),
        ]);

        $this->createProduct('dog', [
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce','en_US', 'just a dog...'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet','en_US', 'dog'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'ecommerce','fr_FR', 'juste un chien...'),
            new SetTextareaValue('a_localizable_scopable_text_area', 'tablet','fr_FR', 'chien'),
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_text_area', Directions::ASCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['cattle', 'cat', 'dog', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_scopable_text_area', Directions::ASCENDING, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['cat', 'cattle', 'dog', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_localizable_scopable_text_area', Directions::DESCENDING, ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
        $this->assertOrder($result, ['dog', 'cat', 'cattle', 'empty_product']);

        $result = $this->executeSorter([['a_localizable_scopable_text_area', Directions::DESCENDING, ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assertOrder($result, ['dog', 'cattle', 'cat', 'empty_product']);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_localizable_scopable_text_area', 'A_BAD_DIRECTION', ['locale' => 'fr_FR', 'scope' => 'tablet']]]);
    }
}
