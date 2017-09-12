<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductModelNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement(SerializerAwareInterface::class);
    }

    function it_supports_flat_normalization_of_product_model(
        ProductModelInterface $productModel,
        ProductInterface $product
    ) {
        $this->supportsNormalization($productModel, 'flat')->shouldBe(true);
        $this->supportsNormalization($productModel, 'json')->shouldBe(false);
        $this->supportsNormalization($product, 'flat')->shouldBe(false);
    }

    function it_normalizes_a_product_model(
        Serializer $serializer,
        ProductModelInterface $productModel,
        ValueInterface $sku,
        Collection $values,
        \Iterator $iterator
    ) {
        $this->setSerializer($serializer);

        $productModel->getCode()->willReturn($sku);
        $productModel->getCategoryCodes()->willReturn(['nice shoes', 'converse']);
        $productModel->getValues()->willReturn($values);

        $values->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, false);
        $iterator->current()->willReturn($sku);
        $iterator->next()->shouldBeCalled();

        $serializer->normalize($sku, 'flat', Argument::any())->willReturn(['sku' => 'sku-001']);

        $this->normalize($productModel, 'flat', [])->shouldReturn(
            [
                'categories' => 'nice shoes,converse',
                'sku'        => 'sku-001',
            ]
        );
    }
}
