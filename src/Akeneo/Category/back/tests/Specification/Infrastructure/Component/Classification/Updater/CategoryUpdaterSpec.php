<?php

namespace Specification\Akeneo\Category\Infrastructure\Component\Classification\Updater;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Updater\CategoryUpdater;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\IsCategoryTreeLinkedToChannel;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Query\PublicApi\IsCategoryTreeLinkedToUser;
use PhpSpec\ObjectBehavior;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class CategoryUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $categoryRepository,
        IsCategoryTreeLinkedToUser $isCategoryTreeLinkedToUser,
        IsCategoryTreeLinkedToChannel $isCategoryTreeLinkedToChannel
    ) {
        $this->beConstructedWith(
            $categoryRepository,
            $isCategoryTreeLinkedToUser,
            $isCategoryTreeLinkedToChannel
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_category()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                CategoryInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_not_translatable_category(
        $categoryRepository,
        CategoryInterface $category,
        CategoryInterface $categoryMaster,
        CategoryTranslation $translatable
    ) {
        $categoryRepository->findOneByIdentifier('master')->willReturn($categoryMaster);
        $category->setCode('mycode')->shouldBeCalled();
        $category->setParent($categoryMaster)->shouldBeCalled();
        $category->getId()->willReturn(null);

        $values = [
            'code'         => 'mycode',
            'parent'       => 'master'
        ];

        $this->update($category, $values, []);
    }

    function it_updates_a_null_parent_category(CategoryInterface $category)
    {
        $category->setCode('mycode')->shouldBeCalled();
        $category->setParent(null)->shouldBeCalled();

        $values = [
            'code'   => 'mycode',
            'parent' => null
        ];

        $this->update($category, $values, []);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(CategoryInterface $category)
    {
        $values = [
            'non_existent_field' => 'field',
        ];

        $this
            ->shouldThrow(UnknownPropertyException::unknownProperty('non_existent_field', new NoSuchPropertyException()))
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_trying_to_update_an_unknown_parent_category(
        $categoryRepository,
        CategoryInterface $category
    ) {
        $categoryRepository->findOneByIdentifier('unknown')->willReturn(null);

        $values = [
            'parent' => 'unknown',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::validEntityCodeExpected(
                    'parent',
                    'category code',
                    'The category does not exist',
                    CategoryUpdater::class,
                    'unknown'
                )
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_moving_a_root_category_still_linked_to_a_user(
        $categoryRepository,
        $isCategoryTreeLinkedToUser,
        CategoryInterface $category,
        CategoryInterface $categoryMaster
    ) {
        $categoryRepository->findOneByIdentifier('master')->willReturn($categoryMaster);

        $category->getId()->willReturn(1);
        $category->isRoot()->willReturn(true);

        $isCategoryTreeLinkedToUser->byCategoryTreeId(1)->willReturn(true);

        $values = [
            'parent' => 'master',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::expected(
                    'You can\'t move a category tree linked to a user.',
                    CategoryUpdater::class
                )
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_moving_a_root_category_still_linked_to_a_channel(
        $categoryRepository,
        $isCategoryTreeLinkedToUser,
        $isCategoryTreeLinkedToChannel,
        CategoryInterface $category,
        CategoryInterface $categoryMaster
    ) {
        $categoryRepository->findOneByIdentifier('master')->willReturn($categoryMaster);

        $category->getId()->willReturn(1);
        $category->isRoot()->willReturn(true);

        $isCategoryTreeLinkedToUser->byCategoryTreeId(1)->willReturn(false);
        $isCategoryTreeLinkedToChannel->byCategoryTreeId(1)->willReturn(true);

        $values = [
            'parent' => 'master',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::expected(
                    'You can\'t move a category tree linked to a channel.',
                    CategoryUpdater::class
                )
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_code_is_not_a_scalar(CategoryInterface $category)
    {
        $values = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('code', CategoryUpdater::class, [])
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_parent_is_not_a_scalar(CategoryInterface $category)
    {
        $values = [
            'parent' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('parent', CategoryUpdater::class, [])
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(CategoryInterface $category)
    {
        $values = [
            'labels' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('labels', CategoryUpdater::class, 'foo')
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_one_of_the_labels_in_label_property_is_not_a_scalar(CategoryInterface $category)
    {
        $values = [
            'labels' => [
                'en_US' => 'foo',
                'fr_FR' => [],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'labels',
                    'one of the labels is not a scalar',
                    CategoryUpdater::class,
                    $values['labels']
                )
            )
            ->during('update', [$category, $values, []]);
    }

    function it_throws_an_exception_when_a_property_is_unknown(CategoryInterface $category)
    {
        $values = [
            'unknown' => 'foo',
        ];

        $this
            ->shouldThrow(
                UnknownPropertyException::unknownProperty('unknown')
            )
            ->during('update', [$category, $values, []]);
    }
}
