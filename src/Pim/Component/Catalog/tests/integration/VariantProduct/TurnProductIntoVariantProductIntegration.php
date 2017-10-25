<?php

namespace Pim\Component\Catalog\tests\integration\VariantProduct;

use Akeneo\Test\Integration\TestCase;

class TurnProductIntoVariantProductIntegration extends TestCase
{
    public function it_turns_a_product_into_a_variant_product()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('foo', '');
    }
}
