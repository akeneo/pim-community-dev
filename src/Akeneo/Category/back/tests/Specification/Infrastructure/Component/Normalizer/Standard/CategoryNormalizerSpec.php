<?php

namespace Specification\Akeneo\Category\Infrastructure\Component\Normalizer\Standard;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Normalizer\Standard\CategoryNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->beConstructedWith($translationNormalizer, $dateTimeNormalizer);
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

    function it_normalizes_category(
        $translationNormalizer,
        CategoryInterface $category,
        DateTimeNormalizer $dateTimeNormalizer
    ) {
        $updated = new \DateTime('2016-06-14T13:12:50');

        $category->getCode()->willReturn('my_code');
        $category->getParent()->willReturn(null);
        $category->getUpdated()->willReturn($updated);

        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);
        $dateTimeNormalizer->normalize($updated, null)->willReturn(
            '2016-06-14T13:12:50+01:00'
        );

        $this->normalize($category)->shouldReturn(
            [
                'code' => 'my_code',
                'parent' => null,
                'updated' => '2016-06-14T13:12:50+01:00',
                'labels' => [],
            ]
        );
    }

    function it_normalizes_category_with_parent(
        $translationNormalizer,
        CategoryInterface $category,
        CategoryInterface $parent,
        DateTimeNormalizer $dateTimeNormalizer
    ) {

        $updated = new \DateTime('2016-06-14T13:12:50');

        $category->getCode()->willReturn('my_code');
        $category->getParent()->willReturn($parent);
        $parent->getCode()->willReturn('my_parent');
        $category->getUpdated()->willReturn($updated);

        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);
        $dateTimeNormalizer->normalize($updated, null)->willReturn(
            '2016-06-14T13:12:50+01:00'
        );

        $this->normalize($category)->shouldReturn(
            [
                'code' => 'my_code',
                'parent' => 'my_parent',
                'updated' => '2016-06-14T13:12:50+01:00',
                'labels' => [],
            ]
        );
    }
}
