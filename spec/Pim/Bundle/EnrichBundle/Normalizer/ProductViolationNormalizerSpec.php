<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ProductViolationNormalizerSpec extends ObjectBehavior
{
    function it_supports_constraint_violation(ConstraintViolationInterface $violation)
    {
        $this->supportsNormalization($violation, 'internal_api')->shouldReturn(true);
    }

    function it_normlizes_constraint_violation_with_scope_and_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn(['description-en_US-mobile' => $productValue]);
        $productValue->getLocale()->willReturn('en_US');
        $productValue->getScope()->willReturn('mobile');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('description');

        $violation->getPropertyPath()->willReturn('values[description-en_US-mobile].text');
        $violation->getMessage()->willReturn('The text is too long.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'description',
            'locale'    => 'en_US',
            'scope'     => 'mobile',
            'message'   => 'The text is too long.'
        ]);
    }

    function it_normalizes_constraint_violation_with_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $violation->getRoot()->willReturn($product);
        $product->getValues()->willReturn(['movie-title-fr_FR' => $productValue]);
        $productValue->getLocale()->willReturn('fr_FR');
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('movie-title');

        $violation->getPropertyPath()->willReturn('values[movie-title-fr_FR].varchar');
        $violation->getMessage()->willReturn('This movie title is very bad.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'movie-title',
            'locale'    => 'fr_FR',
            'scope'     => null,
            'message'   => 'This movie title is very bad.'
        ]);
    }

    function it_normalizes_constraint_violation_with_scope(
        ConstraintViolationInterface $violation,
        ProductInterface $product,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn(['name-ecommerce' => $productValue]);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn('ecommerce');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $violation->getPropertyPath()->willReturn('values[name-ecommerce].varchar');
        $violation->getMessage()->willReturn('The text is too short.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'name',
            'locale'    => null,
            'scope'     => 'ecommerce',
            'message'   => 'The text is too short.'
        ]);
    }

    function it_normalizes_constraint_violation(
        ConstraintViolationInterface $violation,
        ProductInterface $product,
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $product->getValues()->willReturn(['price' => $productValue]);
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('price');

        $violation->getPropertyPath()->willReturn('values[price].float');
        $violation->getMessage()->willReturn('The price should be above 10.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'price',
            'locale'    => null,
            'scope'     => null,
            'message'   => 'The price should be above 10.'
        ]);
    }

    function it_normalizes_global_violation(ConstraintViolationInterface $violation, ProductInterface $product)
    {
        $violation->getPropertyPath()->willReturn('variant.color');
        $violation->getMessage()->willReturn('Variant group already contains this color.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'global'  => true,
            'message' => 'Variant group already contains this color.'
        ]);
    }

    function it_throws_an_exception_if_product_argument_is_missing(ConstraintViolationInterface $violation)
    {
        $violation->getPropertyPath()->willReturn('values[price].float');

        $this
            ->shouldThrow(new \InvalidArgumentException( 'Expects a Pim\Component\Catalog\Model\ProductInterface'))
            ->duringNormalize($violation, 'internal_api');
    }

    function it_throws_an_exception_if_product_argument_is_not_a_product(ConstraintViolationInterface $violation)
    {
        $violation->getPropertyPath()->willReturn('values[price].float');

        $this
            ->shouldThrow(new \InvalidArgumentException( 'Expects a Pim\Component\Catalog\Model\ProductInterface'))
            ->duringNormalize($violation, 'internal_api', ['product' => new ProductValue()]);
    }
}
