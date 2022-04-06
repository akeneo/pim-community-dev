<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Category\Manager\PositionResolverInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Normalizer\ExternalApi\CategoryNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer, PositionResolverInterface $positionResolver)
    {
        $this->beConstructedWith($stdNormalizer, $positionResolver);
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

        $normalizedCategory->shouldHaveLabels();
    }

    function it_normalizes_a_category_with_position(
        $stdNormalizer,
        CategoryInterface $category,
        PositionResolverInterface $positionResolver
    ) {
        $aPosition = 1;
        $aLevelInDatabase = 1;
        $aLevel = $aLevelInDatabase + 1;
        $context = ['with_position'];
        $data = ['code' => 'my_category', 'labels' => []];

        $category->getLevel()->willReturn($aLevelInDatabase);
        $stdNormalizer->normalize($category, 'standard', $context)->willReturn($data);
        $positionResolver->getPosition($category)->willReturn($aPosition);

        $normalizedCategory = $this->normalize($category, 'external_api', $context);

        $normalizedCategory->shouldHaveLabels();
        $normalizedCategory->shouldHavePosition($aPosition);
        $normalizedCategory->shouldHaveLevel($aLevel);
    }

    public function getMatchers(): array
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            },
            'havePosition' => function ($subject, $position) {
                return array_key_exists('position', $subject) && $position === $subject['position'];
            },
            'haveLevel' => function ($subject, $level) {
                return array_key_exists('level', $subject) && $level === $subject['level'];
            },
        ];
    }
}
