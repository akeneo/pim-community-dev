<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\CategoryNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
