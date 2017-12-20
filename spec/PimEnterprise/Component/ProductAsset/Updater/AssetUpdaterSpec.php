<?php

namespace spec\PimEnterprise\Component\ProductAsset\Updater;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Component\Classification\Repository\TagRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use PimEnterprise\Component\ProductAsset\Updater\AssetUpdater;
use Prophecy\Argument;

class AssetUpdaterSpec extends ObjectBehavior
{
    function let(
        TagRepositoryInterface $tagRepository,
        CategoryRepositoryInterface $categoryRepository,
        AssetFactory $assetFactory
    ) {
        $this->beConstructedWith($tagRepository, $categoryRepository, $assetFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Updater\AssetUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_asset()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'PimEnterprise\Component\ProductAsset\Model\AssetInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_an_asset(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1,
        TagInterface $tag2,
        CategoryInterface $cat1,
        CategoryInterface $cat2
    ) {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();
        $asset->setEndOfUseAt(new \DateTime('2018-02-01T00:00:00+01:00'))->shouldBeCalled();

        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $tagRepository->findOneByIdentifier('flowers')->willReturn($tag2);

        $categoryRepository->findOneByIdentifier('cat1')->willReturn($cat1);
        $categoryRepository->findOneByIdentifier('cat2')->willReturn($cat2);

        $asset->getTagCodes()->willReturn([]);
        $asset->getCategoryCodes()->willReturn([]);
        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();

        $asset->addCategory($cat1)->shouldBeCalled();
        $asset->addCategory($cat2)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2'],
            'end_of_use'  => '2018-02-01T00:00:00+01:00',
        ];

        $this->update($asset, $values, []);
    }

    function it_throws_an_exception_if_tag_does_not_exist($tagRepository, AssetInterface $asset)
    {
        $tagRepository->findOneByIdentifier('dog')->willReturn(null);

        $asset->getTagCodes()->willReturn('');
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog'],
            'categories'  => ['cat1'],
            'end_of_use'  => '2018-02-01',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::validEntityCodeExpected(
                    'tags',
                    'tag code',
                    'The tag does not exist',
                    'PimEnterprise\Component\ProductAsset\Updater\AssetUpdater',
                    'dog'
                )
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_category_does_not_exist(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1
    ) {
        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $categoryRepository->findOneByIdentifier('cat1')->willReturn(null);

        $asset->getCategoryCodes()->willReturn([]);
        $asset->getTagCodes()->willReturn([]);
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog'],
            'categories'  => ['cat1'],
            'end_of_use'  => '2018-02-01',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::validEntityCodeExpected(
                    'categories',
                    'category code',
                    'The category does not exist',
                    'PimEnterprise\Component\ProductAsset\Updater\AssetUpdater',
                    'cat1'
                )
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_date_format_is_invalid(
        $tagRepository,
        $categoryRepository,
        AssetInterface $asset,
        TagInterface $tag1,
        TagInterface $tag2,
        CategoryInterface $cat1,
        CategoryInterface $cat2
    ) {
        $asset->setCode('mycode')->shouldBeCalled();
        $asset->setDescription('My awesome description')->shouldBeCalled();
        $asset->getTagCodes()->willReturn([]);
        $asset->getCategoryCodes()->willReturn([]);

        $tagRepository->findOneByIdentifier('dog')->willReturn($tag1);
        $tagRepository->findOneByIdentifier('flowers')->willReturn($tag2);

        $categoryRepository->findOneByIdentifier('cat1')->willReturn($cat1);
        $categoryRepository->findOneByIdentifier('cat2')->willReturn($cat2);

        $asset->getTags()->willReturn(new ArrayCollection([]));
        $asset->getCategories()->willReturn(new ArrayCollection([]));

        $asset->addTag($tag1)->shouldBeCalled();
        $asset->addTag($tag2)->shouldBeCalled();

        $asset->addCategory($cat1)->shouldBeCalled();
        $asset->addCategory($cat2)->shouldBeCalled();

        $asset->getId()->willReturn(null);

        $values = [
            'code'        => 'mycode',
            'description' => 'My awesome description',
            'tags'        => ['dog', 'flowers'],
            'categories'  => ['cat1', 'cat2'],
            'end_of_use'  => '2018/02/01',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyException::dateExpected(
                    'end_of_use',
                    \DateTime::ISO8601,
                    AssetUpdater::class,
                    '2018/02/01'
                )
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_categories_property_is_not_an_array(AssetInterface $asset) {
        $values = [
            'categories'  => 'category_1',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('categories', AssetUpdater::class, 'category_1')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_categories_property_is_not_an_array_of_scalar(AssetInterface $asset) {
        $values = [
            'categories'  => ['category_1', ['category_2']]
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'categories',
                    'one of the "categories" values is not a scalar',
                    AssetUpdater::class,
                   ['category_1', ['category_2']]
                )
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_tags_property_is_not_an_array(AssetInterface $asset) {
        $values = [
            'tags'  => 'tag_1',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected('tags', AssetUpdater::class, 'tag_1')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_tags_property_is_not_an_array_of_scalar(AssetInterface $asset) {
        $values = [
            'tags'  => ['tag_1', ['tag_2']]
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'tags',
                    'one of the "tags" values is not a scalar',
                    AssetUpdater::class,
                    ['tag_1', ['tag_2']]
                )
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_code_property_is_not_a_scalar(AssetInterface $asset) {
        $values = ['code'  => ['my_code']];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('code', AssetUpdater::class, ['my_code'])
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_end_of_use_property_is_not_a_scalar(AssetInterface $asset) {
        $values = ['end_of_use'  => ['2018-02-01T00:00:00+01:00']];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('end_of_use', AssetUpdater::class, ['2018-02-01T00:00:00+01:00'])
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_description_is_not_a_scalar(AssetInterface $asset) {
        $values = ['description'  => ['description']];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected('description', AssetUpdater::class, ['description'])
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_localized_is_not_a_scalar(AssetInterface $asset) {
        $values = ['localized'  => 'foo'];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::booleanExpected('localized', AssetUpdater::class, 'foo')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_if_unknown_property_to_update(AssetInterface $asset) {
        $values = ['unknown_property'  => 'unknown_property'];

        $this
            ->shouldThrow(
                UnknownPropertyException::unknownProperty('unknown_property')
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_when_updating_an_existing_unlocalizable_asset_as_localizable(AssetInterface $asset) {
        $values = ['localized'  => true];

        $asset->getId()->willReturn(1);
        $asset->isLocalizable()->willReturn(false);

        $this
            ->shouldThrow(
                ImmutablePropertyException::immutableProperty('localized', true, AssetUpdater::class)
            )
            ->during('update', [$asset, $values, []]);
    }

    function it_throws_an_exception_when_updating_an_existing_localizable_asset_as_unlocalizable(AssetInterface $asset) {
        $values = ['localized'  => false];

        $asset->getId()->willReturn(1);
        $asset->isLocalizable()->willReturn(true);

        $this
            ->shouldThrow(
                ImmutablePropertyException::immutableProperty('localized', false, AssetUpdater::class)
            )
            ->during('update', [$asset, $values, []]);
    }
}
