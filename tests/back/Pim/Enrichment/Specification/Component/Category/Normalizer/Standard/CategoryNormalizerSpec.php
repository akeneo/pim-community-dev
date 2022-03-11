<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Category\Normalizer\Standard\CategoryNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer,
                 DateTimeNormalizer $dateTimeNormalizer,
                 ApiResourceRepositoryInterface $apiResourceRepository)
    {
        $this->beConstructedWith($translationNormalizer, $dateTimeNormalizer, $apiResourceRepository);
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
        CategoryInterface $rootCategory,
        DateTimeNormalizer $dateTimeNormalizer,
        ApiResourceRepositoryInterface $apiResourceRepository
    )
    {
        $updated = new \DateTime('2016-06-14T13:12:50');
        $aParent = null;
        $aCategoryCode = 'my_code';
        $aLeft = 1;
        $aRight = 3;
        $aRoot = 1;
        $aRootCategoryCode = 'a_root_category_code';
        $aLevel = 2;

        $category->getCode()->willReturn($aCategoryCode);
        $category->getLevel()->willReturn($aLevel);
        $category->getRoot()->willReturn($aRoot);
        $category->getLeft()->willReturn($aLeft);
        $category->getRight()->willReturn($aRight);
        $category->getParent()->willReturn($aParent);
        $category->getUpdated()->willReturn($updated);

        $rootCategory->getCode()->willReturn($aRootCategoryCode);

        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);
        $dateTimeNormalizer->normalize($updated, null)->willReturn(
            $updated->format(\DateTime::ATOM)
        );

        $apiResourceRepository->find($aRoot)->willReturn($rootCategory);

        $this->normalize($category)->shouldReturn(
            [
                'code' => $aCategoryCode,
                'root' => $aRootCategoryCode,
                'parent' => $aParent,
                'updated' => $updated->format(\DateTime::ATOM),
                'labels' => [],
                'nested_tree_node' => [
                    'depth' => $aLevel,
                    'left' => $aLeft,
                    'right' => $aRight
                ]
            ]
        );
    }

    function it_normalizes_category_with_parent(
        $translationNormalizer,
        CategoryInterface $category,
        CategoryInterface $rootCategory,
        CategoryInterface $parent,
        DateTimeNormalizer $dateTimeNormalizer,
        ApiResourceRepositoryInterface $apiResourceRepository
    )
    {
        $updated = new \DateTime('2016-06-14T13:12:50');
        $aCategoryCode = 'my_code';
        $aLeft = 1;
        $aRight = 3;
        $aRoot = 1;
        $aRootCategoryCode = 'a_root_category_code';
        $aParentCode = 'a_parent_code';
        $aLevel = 2;

        $category->getCode()->willReturn($aCategoryCode);
        $category->getLevel()->willReturn($aLevel);
        $category->getRoot()->willReturn($aRoot);
        $category->getLeft()->willReturn($aLeft);
        $category->getRight()->willReturn($aRight);
        $category->getParent()->willReturn($aParentCode);
        $category->getUpdated()->willReturn($updated);
        $rootCategory->getCode()->willReturn($aRootCategoryCode);
        $category->getParent()->willReturn($parent);
        $parent->getCode()->willReturn($aParentCode);

        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);
        $dateTimeNormalizer->normalize($updated, null)->willReturn(
            $updated->format(\DateTime::ATOM)
        );

        $apiResourceRepository->find($aRoot)->willReturn($rootCategory);

        $this->normalize($category)->shouldReturn(
            [
                'code' => $aCategoryCode,
                'root' => $aRootCategoryCode,
                'parent' => $aParentCode,
                'updated' => $updated->format(\DateTime::ATOM),
                'labels' => [],
                'nested_tree_node' => [
                    'depth' => $aLevel,
                    'left' => $aLeft,
                    'right' => $aRight
                ]
            ]
        );
    }

    function it_normalizes_category_with_no_root_category(
        $translationNormalizer,
        CategoryInterface $category,
        DateTimeNormalizer $dateTimeNormalizer,
        ApiResourceRepositoryInterface $apiResourceRepository
    )
    {
        $updated = new \DateTime('2016-06-14T13:12:50');
        $aParent = null;
        $aCategoryCode = 'my_code';
        $aLeft = 1;
        $aRight = 3;
        $aRoot = null;
        $aLevel = 2;

        $category->getCode()->willReturn($aCategoryCode);
        $category->getLevel()->willReturn($aLevel);
        $category->getRoot()->willReturn($aRoot);
        $category->getLeft()->willReturn($aLeft);
        $category->getRight()->willReturn($aRight);
        $category->getParent()->willReturn($aParent);
        $category->getUpdated()->willReturn($updated);

        $translationNormalizer->normalize($category, 'standard', [])->willReturn([]);
        $dateTimeNormalizer->normalize($updated, null)->willReturn(
            $updated->format(\DateTime::ATOM)
        );

        $apiResourceRepository->find($aRoot)->willReturn(null);

        $this->normalize($category)->shouldReturn(
            [
                'code' => $aCategoryCode,
                'root' => null,
                'parent' => $aParent,
                'updated' => $updated->format(\DateTime::ATOM),
                'labels' => [],
                'nested_tree_node' => [
                    'depth' => $aLevel,
                    'left' => $aLeft,
                    'right' => $aRight
                ]
            ]
        );
    }
}
