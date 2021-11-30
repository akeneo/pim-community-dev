<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\LruCachedSelectOptionCollectionRepository;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LruCachedSelectOptionCollectionRepositorySpec extends ObjectBehavior
{
    function let(SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $this->beConstructedWith($selectOptionCollectionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(SelectOptionCollectionRepository::class);
        $this->shouldImplement(CachedQueryInterface::class);
        $this->shouldHaveType(LruCachedSelectOptionCollectionRepository::class);
    }

    function it_saves_an_option_collection_and_clears_the_cache(
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $optionCollection = SelectOptionCollection::fromNormalized([['code' => 'foo'], ['code' => 'bar']]);
        $writeOptionCollection = WriteSelectOptionCollection::fromReadSelectOptionCollection($optionCollection);
        $columnCode = ColumnCode::fromString('ingredient');
        $selectOptionCollectionRepository->save('nutrition', $columnCode, $writeOptionCollection)->shouldBeCalled();

        $this->save('nutrition', $columnCode, $writeOptionCollection);

        $selectOptionCollectionRepository->getByColumn('nutrition', $columnCode)->shouldBeCalled()->willReturn(
            $optionCollection
        );
        $this->getByColumn('nutrition', $columnCode)->shouldReturn($optionCollection);
    }

    function it_upserts_an_option_collection_and_clears_the_cache(SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $optionCollection = SelectOptionCollection::fromNormalized([['code' => 'foo'], ['code' => 'bar']]);
        $columnCode = ColumnCode::fromString('ingredient');
        $selectOptionCollectionRepository->upsert('nutrition', $columnCode, $optionCollection)->shouldBeCalled();

        $this->upsert('nutrition', $columnCode, $optionCollection);

        $selectOptionCollectionRepository->getByColumn('nutrition', $columnCode)->shouldBeCalled()->willReturn(
            $optionCollection
        );
        $this->getByColumn('nutrition', $columnCode)->shouldReturn($optionCollection);
    }

    function it_gets_a_select_option_collection_by_doing_a_query_if_the_cache_is_not_hit(
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $columnCode = ColumnCode::fromString('ingredient');
        $optionCollection = SelectOptionCollection::fromNormalized([['code' => 'toto']]);
        $selectOptionCollectionRepository->getByColumn('nutrition', $columnCode)->shouldBeCalled()->willReturn(
            $optionCollection
        );

        $this->getByColumn('nutrition', ColumnCode::fromString('ingredient'))->shouldReturn($optionCollection);
    }

    function it_gets_a_select_option_collection_from_the_cache_when_it_is_hit(
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $columnCode = ColumnCode::fromString('ingredient');
        $optionCollection = SelectOptionCollection::fromNormalized([['code' => 'toto']]);
        $selectOptionCollectionRepository->getByColumn(Argument::cetera())->shouldBeCalledOnce()->willReturn(
            $optionCollection
        );

        $this->getByColumn('nutrition', $columnCode)->shouldReturn($optionCollection);
        $this->getByColumn('nutrition', $columnCode)->shouldReturn($optionCollection);
        $this->getByColumn('nutrition', $columnCode)->shouldReturn($optionCollection);
    }
}
