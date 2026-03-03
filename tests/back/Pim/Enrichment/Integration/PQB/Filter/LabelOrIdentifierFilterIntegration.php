<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @author    Julien Sanchez <julien@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * We search labels and identifiers both on products and product models and
     * check that we get both in the same result
     */
    public function testSearchContains(): void
    {
        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, ['model-braided-hat', '1111111240', 'braided-hat-m', 'braided-hat-xxxl']);

        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'ha', ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, [
            'model-braided-hat',
            'hades',
            'braided-hat-m',
            'braided-hat-xxxl',
            '1111111234',
            '1111111235',
            '1111111236',
            '1111111237',
            '1111111238',
            '1111111239',
            '1111111240',
            'hades_blue',
            'hades_red',
        ]);

        $sampleProduct = $this->createProduct('sample', [new SetFamily('accessories')]);
        $sampleBProduct = $this->createProduct('sampleB', [new SetFamily('accessories')]);
        $sampleRedProduct = $this->createProduct('sample_red', [new SetFamily('accessories')]);

        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'sample', ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, [
            'sample',
            'sampleB',
            'sample_red',
        ]);

        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'sample_', ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, [
            'sample_red',
        ]);
    }

    public function testSearchOnLabelAndCompleteness(): void
    {
        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']],
            ['completeness', Operators::AT_LEAST_COMPLETE, null, ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, ['model-braided-hat', 'braided-hat-m', 'braided-hat-xxxl']);
    }

    public function testSearchByUuid(): void
    {
        $uuid = $this->getProductUuid('1111111234');

        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, $uuid->toString()]
        ]);
        $this->assert($result, ['1111111234']);
    }
    
    public function testSearchOnIdentifierValues(): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'my_identifier',
            'type' => AttributeTypes::IDENTIFIER,
            'group' => 'other',
            'useable_as_grid_filter' => true,
        ]);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->createProduct('one', [
            new SetFamily('accessories'),
            new SetIdentifierValue('my_identifier', 'toto')
        ]);
        $this->createProduct('two', [
            new SetFamily('accessories'),
            new SetIdentifierValue('my_identifier', 'totu')
        ]);
        $this->createProduct('three', [
            new SetFamily('accessories'),
            new SetIdentifierValue('my_identifier', 'foo')
        ]);

        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'tot']
        ]);
        $this->assert($result, ['one', 'two']);
    }
}
