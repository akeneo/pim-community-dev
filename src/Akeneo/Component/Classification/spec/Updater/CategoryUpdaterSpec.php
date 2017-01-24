<?php

namespace spec\Akeneo\Component\Classification\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class CategoryUpdaterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Classification\Updater\CategoryUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_category()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Akeneo\Component\Classification\Model\CategoryInterface'
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
                    'updater',
                    'category',
                    'unknown'
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
                InvalidPropertyTypeException::scalarExpected('code', 'update', 'category', [])
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
                InvalidPropertyTypeException::scalarExpected('parent', 'update', 'category', [])
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
                InvalidPropertyTypeException::arrayExpected('labels', 'update', 'category', 'foo')
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
                    'update',
                    'category',
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
