<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\Media;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

class PricesCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_prices_collection_in_the_proposal_format(
        Collection $collection
    ) {
        $collection->count()->willReturn(1);
        $collection->filter(Argument::any())->willReturn($collection);

        $this->supportsNormalization($collection, 'proposal')->shouldBe(true);
    }

    function it_normalizes_collection_object(
        Collection $collection,
        ProductPrice $usd,
        ProductPrice $eur
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([$usd->getWrappedObject(), $eur->getWrappedObject()]));
        $usd->getData()->willReturn(4.8);
        $usd->getCurrency()->willReturn('USD');
        $eur->getData()->willReturn(15.16);
        $eur->getCurrency()->willReturn('EUR');

        $this->normalize($collection, 'proposal')->shouldReturn(
            [
                'USD' => 4.8,
                'EUR' => 15.16,
            ]
        );
    }

    function it_supports_denormalization_of_price_collection_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_price_collection', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_array_data_into_the_context_price_collection(
        Collection $collection,
        ProductPrice $usd,
        ProductPrice $eur
    ) {
        $collection->offsetGet('USD')->willReturn($usd);
        $collection->offsetGet('EUR')->willReturn($eur);

        $usd->setData(4.8)->shouldBeCalled();
        $eur->setData(15.16)->shouldBeCalled();

        $this->denormalize(['USD' => 4.8, 'EUR' => 15.16], 'pim_catalog_price_collection', 'proposal', ['instance' => $collection])->shouldReturn($collection);
    }
}
