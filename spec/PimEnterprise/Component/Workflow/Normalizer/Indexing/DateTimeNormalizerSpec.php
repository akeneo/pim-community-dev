<?php

namespace spec\PimEnterprise\Component\Workflow\Normalizer\Indexing;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\DateTimeNormalizer;
use PimEnterprise\Component\Workflow\Normalizer\Indexing\ProductProposalNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $standardNormalizer)
    {
        $this->beConstructedWith($standardNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeNormalizer::class);
    }

    function it_supports_dates()
    {
        $date = new \DateTime();
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(false);

        $this->supportsNormalization($date, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($date, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)->shouldReturn(true);
        $this->supportsNormalization($date, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX)
            ->shouldReturn(true);
    }

    function it_normalizes_dates($standardNormalizer)
    {
        $date = new \DateTime();
        $standardNormalizer->normalize($date, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX, ['context'])
            ->willReturn('date');

        $this->normalize($date, ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX, ['context'])
            ->shouldReturn('date');
    }
}
