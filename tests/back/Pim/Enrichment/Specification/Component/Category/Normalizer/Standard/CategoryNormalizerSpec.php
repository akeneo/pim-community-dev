<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard\CategoryNormalizer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_normalization(CategoryInterface $category)
    {
        $this->supportsNormalization($category, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($category, 'xml')->shouldReturn(false);
        $this->supportsNormalization($category, 'json')->shouldReturn(false);
    }

    function it_normalizes_category($translationNormalizer, CategoryInterface $category)
    {
        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);

        $category->getCode()->willReturn('my_code');
        $category->getParent()->willReturn(null);

        $this->normalize($category)->shouldReturn([
            'code'   => 'my_code',
            'parent' => null,
            'labels' => []
        ]);
    }

    function it_normalizes_category_with_parent(
        $translationNormalizer,
        CategoryInterface $category,
        CategoryInterface $parent
    ) {
        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);

        $category->getCode()->willReturn('my_code');
        $category->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('my_parent');

        $this->normalize($category)->shouldReturn([
            'code'   => 'my_code',
            'parent' => 'my_parent',
            'labels' => []
        ]);
    }
}
