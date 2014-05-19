<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model;

class ProductChangesApplierSpec extends ObjectBehavior
{
    public function let(DenormalizerInterface $denormalizer)
    {
        $this->beConstructedWith($denormalizer);
    }

    function it_uses_denormlizer_to_apply_data_to_a_product_value(
        $denormalizer,
        Model\AbstractProduct $product
    ) {
        $denormalizer->denormalize(['foo' => 'bar'], 'product', 'proposal', ['instance' => $product])->shouldBeCalled();

        $this->apply($product, ['foo' => 'bar']);
    }
}
