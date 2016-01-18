<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 * @require \MongoId
 */
class ProductPriceNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory)
    {
        $this->beConstructedWith($mongoFactory);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_price_in_mongodb_document(ProductPrice $price)
    {
        $this->supportsNormalization($price, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_price_into_other_format(ProductPrice $price)
    {
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_price_into_mongodb_document(
        $mongoFactory,
        ProductPrice $price,
        \MongoId $mongoId
    ) {
        $mongoFactory->createMongoId()->willReturn($mongoId);

        $price->getCurrency()->willReturn('USD');
        $price->getData()->willReturn(9.99);

        $this->normalize($price, 'mongodb_document')->shouldReturn([
            '_id'      => $mongoId,
            'currency' => 'USD',
            'data'     => 9.99
        ]);
    }
}
