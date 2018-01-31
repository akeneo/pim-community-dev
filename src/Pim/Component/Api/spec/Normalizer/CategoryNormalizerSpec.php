<?php

namespace spec\Pim\Component\Api\Normalizer;

use Akeneo\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Api\Normalizer\CategoryNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryNormalizer::class);
    }

    function it_supports_a_category(CategoryInterface $category)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($category, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($category, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_category($stdNormalizer, CategoryInterface $category)
    {
        $data = ['code' => 'my_category', 'labels' => []];

        $stdNormalizer->normalize($category, 'standard', [])->willReturn($data);

        $normalizedCategory = $this->normalize($category, 'external_api', []);
        $normalizedCategory->shouldHaveLabels($data);
    }

    public function getMatchers()
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            }
        ];
    }
}
