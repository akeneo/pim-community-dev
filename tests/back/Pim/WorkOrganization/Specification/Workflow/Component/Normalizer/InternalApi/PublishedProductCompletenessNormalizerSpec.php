<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\PublishedProductCompletenessNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductCompletenessNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_a_published_product_completeness_normalizer()
    {
        $this->shouldHaveType(PublishedProductCompletenessNormalizer::class);
    }

    function it_only_normalizes_published_product_completenesses_for_internal_api_format()
    {
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
        $completeness = new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, []);
        $this->supportsNormalization($completeness, 'any_format')->shouldReturn(false);
        $this->supportsNormalization($completeness, 'internal_api')->shouldReturn(true);
    }

    function it_normalizes_a_published_product_completeness()
    {
        $completeness = new PublishedProductCompleteness(
            'print',
            'zh_CN',
            9,
            ['description', 'picture']
        );
        $this->normalize($completeness, 'internal_api')->shouldReturn(
            [
                'required' => 9,
                'missing' => 2,
                'ratio' => 77,
                'locale' => 'zh_CN',
                'channel' => 'print',
            ]
        );
    }
}
