<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Normalizer\Indexing\CompletenessCollectionNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Prophecy\Argument;

class CompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessCollectionNormalizer::class);
    }

    function it_supports_only_indexing_formats_for_completenesses(\stdClass $toNormalize)
    {
        $this->supportsNormalization(Argument::any(), 'foo')->shouldReturn(false);
        $this->supportsNormalization($toNormalize, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($toNormalize, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_supports_completenesses_for_indexing_formats(
        Collection $completenesses,
        CompletenessInterface $completeness
    ) {
        $completenesses->isEmpty()->willReturn(false);
        $completenesses->first()->willReturn($completeness);

        $this->supportsNormalization($completenesses, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_completenesses(
        Collection $completenesses,
        \ArrayIterator $completenessesIterator,
        CompletenessInterface $completeness1,
        CompletenessInterface $completeness2,
        CompletenessInterface $completeness3,
        CompletenessInterface $completeness4,
        ChannelInterface $ecommerce,
        ChannelInterface $tablet,
        LocaleInterface $enUs,
        LocaleInterface $frFR,
        LocaleInterface $deDE,
        LocaleInterface $itIT
    ) {
        $completenesses->getIterator()->willReturn($completenessesIterator);
        $completenessesIterator->rewind()->shouldBeCalled();
        $completenessesIterator->valid()->willReturn(true, true, true, true, false);
        $completenessesIterator->current()->willReturn($completeness1, $completeness2, $completeness3, $completeness4);
        $completenessesIterator->next()->shouldBeCalled();

        $ecommerce->getCode()->willReturn('ecommerce');
        $tablet->getCode()->willReturn('tablet');
        $enUs->getCode()->willReturn('en_US');
        $frFR->getCode()->willReturn('fr_FR');
        $deDE->getCode()->willReturn('de_DE');
        $itIT->getCode()->willReturn('it_IT');

        $completeness1->getChannel()->willReturn($ecommerce);
        $completeness1->getLocale()->willReturn($enUs);
        $completeness1->getRatio()->willReturn(78);

        $completeness2->getChannel()->willReturn($ecommerce);
        $completeness2->getLocale()->willReturn($frFR);
        $completeness2->getRatio()->willReturn(43);

        $completeness3->getChannel()->willReturn($ecommerce);
        $completeness3->getLocale()->willReturn($deDE);
        $completeness3->getRatio()->willReturn(43);

        $completeness4->getChannel()->willReturn($tablet);
        $completeness4->getLocale()->willReturn($itIT);
        $completeness4->getRatio()->willReturn(78.45);

        $this->normalize($completenesses, 'indexing')->shouldReturn(
            [
                'ecommerce' => [
                    'en_US' => 78,
                    'fr_FR' => 43,
                    'de_DE' => 43,
                ],
                'tablet' => [
                    'it_IT' => 78.45
                ]
            ]
        );
    }
}
