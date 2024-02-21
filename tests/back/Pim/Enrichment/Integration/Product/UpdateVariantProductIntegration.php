<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateVariantProductIntegration extends TestCase
{
    public function testTheParentCannotBeRemoved(): void
    {
        $this->expectException(ImmutablePropertyException::class);
        $this->expectExceptionMessage('Property "parent" cannot be modified, "" given.');

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_blue_xl');
        $this->get('pim_catalog.updater.product')->update($product, ['parent' => '']);
    }

    /**
     * TODO: This will become possible in PIM-6460.
     */
    public function testTheFamilyCannotBeChanged(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_blue_xl');
        $this->get('pim_catalog.updater.product')->update($product, ['family' => 'shoes']);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(1, $errors->count());
        $this->assertEquals(
            'The variant product family must be the same than its parent',
            $errors->get(0)->getMessage()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->get('pim_catalog.builder.product')->createProduct('apollon_blue_xl', 'clothing');
        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'xl',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }
}
