<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ProductViolationNormalizerSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_normalizes_constraint_violation_with_scope_and_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[description-mobile-en_US].text');
        $violation->getMessage()->willReturn('The text is too long.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'description',
            'locale'    => 'en_US',
            'scope'     => 'mobile',
            'message'   => 'The text is too long.'
        ]);
    }

    function it_normalizes_localization_constraint_violation_with_scope_and_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn(
            'values[{"code":"description","locale":"en_US","scope":"mobile"}].text'
        );
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
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[movie_title-<all_channels>-fr_FR].text');
        $violation->getMessage()->willReturn('This movie title is very bad.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'movie_title',
            'locale'    => 'fr_FR',
            'scope'     => null,
            'message'   => 'This movie title is very bad.'
        ]);
    }

    function it_normalizes_localization_constraint_violation_with_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[{"code":"movie_title","locale":"fr_FR","scope":null}].text');
        $violation->getMessage()->willReturn('This movie title is very bad.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'movie_title',
            'locale'    => 'fr_FR',
            'scope'     => null,
            'message'   => 'This movie title is very bad.'
        ]);
    }

    function it_normalizes_constraint_violation_with_scope(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[name-ecommerce-<all_locales>].text');
        $violation->getMessage()->willReturn('The text is too short.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'name',
            'locale'    => null,
            'scope'     => 'ecommerce',
            'message'   => 'The text is too short.'
        ]);
    }

    function it_normalizes_localization_constraint_violation_with_scope(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[{"code":"name","locale":null,"scope":"ecommerce"}].text');
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
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[price-<all_channels>-<all_locales>].float');
        $violation->getMessage()->willReturn('The price should be above 10.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'price',
            'locale'    => null,
            'scope'     => null,
            'message'   => 'The price should be above 10.'
        ]);
    }

    function it_normalizes_constraint_violation_without_channel_and_locale(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[price]');
        $violation->getMessage()->willReturn('The price should be above 10.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'price',
            'locale'    => null,
            'scope'     => null,
            'message'   => 'The price should be above 10.'
        ]);
    }

    function it_normalizes_localization_constraint_violation(
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('values[{"code":"price","locale":null,"scope":null}].float');
        $violation->getMessage()->willReturn('The price should be above 10.');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'price',
            'locale'    => null,
            'scope'     => null,
            'message'   => 'The price should be above 10.'
        ]);
    }

    function it_normalizes_violation_on_product_identifier(
        $attributeRepository,
        ConstraintViolationInterface $violation,
        ProductInterface $product
    ) {
        $violation->getPropertyPath()->willReturn('identifier');
        $violation->getMessage()->willReturn(' This value is already used.');

        $attributeRepository->getIdentifierCode()->willReturn('sku');

        $this->normalize($violation, 'internal_api', ['product' => $product])->shouldReturn([
            'attribute' => 'sku',
            'locale'    => null,
            'scope'     => null,
            'message'   => ' This value is already used.'
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
            ->shouldThrow(new \InvalidArgumentException('Expects a product or product model context'))
            ->duringNormalize($violation, 'internal_api');
    }
}
