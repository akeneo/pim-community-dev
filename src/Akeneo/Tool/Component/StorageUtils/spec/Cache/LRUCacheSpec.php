<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Cache;

use PhpSpec\ObjectBehavior;

/**
 * Note: using a real in memory implementation for the query does not work properly with phpspec to use spy.
 * We have to use an interface for it.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCacheSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(2);
    }

    function it_cannot_be_instantiated_with_zero_or_negative_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [0]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [-1]);
    }

    function it_gets_result_for_single_key_by_calling_the_callable(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery->fromCode('entity_code_3')->willReturn(new EntityObject('entity_code_3'));
        $this->getForKey('entity_code_3', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_3'));
    }

    function it_gets_result_from_single_key_from_the_cache_and_does_not_call_the_callable_query(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery->fromCode('entity_code_1')->willReturn(new EntityObject('entity_code_1'))->shouldBeCalledOnce();
        $this->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_1'));
        $this->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_1'));
    }

    function it_removes_the_least_recently_used_element_when_maximum_size_is_reached(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery->fromCode('entity_code_1')->willReturn(new EntityObject('entity_code_1'))->shouldBeCalledTimes(2);
        $entityObjectQuery->fromCode('entity_code_2')->willReturn(new EntityObject('entity_code_2'))->shouldBeCalledOnce();
        $entityObjectQuery->fromCode('entity_code_3')->willReturn(new EntityObject('entity_code_3'))->shouldBeCalledOnce();

        $this->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_1'));
        $this->getForKey('entity_code_2', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_2'));
        $this->getForKey('entity_code_3', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_3'));
        $this->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))->shouldBeLike(new EntityObject('entity_code_1'));
    }

    /**
     * Test the correct use of array_key_exist instead of isset to handle null values.
     */
    function it_store_null_values_and_does_not_call_the_query_if_null_value_is_stored()
    {
        $query = function(string $entityCode) {
            return null;
        };

        $this->getForKey('entity_code_1', $query)->shouldBe(null);
        $this->getForKey('entity_code_1', $query)->shouldBe(null);
    }

    function it_gets_multiple_keys_by_calling_the_callable(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery
            ->fromCodes(['entity_code_1', 'entity_code_2'])
            ->willReturn(
                [
                    new EntityObject('entity_code_1'),
                    new EntityObject('entity_code_2'),
                ]
            )
            ->shouldBeCalledOnce();

        $this
            ->getForKeys(['entity_code_1', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike([
                new EntityObject('entity_code_1'),
                new EntityObject('entity_code_2'),
            ]);
    }

    function it_does_not_store_all_entries_when_query_result_is_greater_than_cache_size(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery
            ->fromCodes(['entity_code_1', 'entity_code_2', 'entity_code_3'])
            ->willReturn(
                [
                    'entity_code_1' => new EntityObject('entity_code_1'),
                    'entity_code_2' => new EntityObject('entity_code_2'),
                    'entity_code_3' => new EntityObject('entity_code_3'),
                ]
            )
            ->shouldBeCalledOnce();

        $this
            ->getForKeys(['entity_code_1', 'entity_code_2', 'entity_code_3'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike([
                'entity_code_1' => new EntityObject('entity_code_1'),
                'entity_code_2' => new EntityObject('entity_code_2'),
                'entity_code_3' => new EntityObject('entity_code_3'),
            ]);

        $this
            ->getForKey('entity_code_2', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike(new EntityObject('entity_code_2'));

        $entityObjectQuery->fromCode('entity_code_1')->willReturn(new EntityObject('entity_code_1'))->shouldBeCalledOnce();
        $this
            ->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike(new EntityObject('entity_code_1'));
    }

    function it_call_the_callable_only_on_keys_that_are_not_in_the_cache(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery->fromCode('entity_code_1')->willReturn(new EntityObject('entity_code_1'))->shouldBeCalledOnce();
        $this->getForKey('entity_code_1', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()));

        $entityObjectQuery
            ->fromCodes(['entity_code_2'])
            ->willReturn(['entity_code_2' => new EntityObject('entity_code_2')])
            ->shouldBeCalledOnce();

        $this
            ->getForKeys(['entity_code_1', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike([
                'entity_code_1' => new EntityObject('entity_code_1'),
                'entity_code_2' => new EntityObject('entity_code_2'),
            ]);
    }

    function it_handles_string_keys_that_are_numeric(EntityObjectQuery $entityObjectQuery)
    {
        $entityObjectQuery->fromCode('123')->willReturn(new EntityObject('123'))->shouldBeCalledOnce();
        $this->getForKey('123', $this->queryToFetchEntityFromCode($entityObjectQuery->getWrappedObject()));

        $entityObjectQuery
            ->fromCodes(['entity_code_2'])
            ->willReturn(['entity_code_2' => new EntityObject('entity_code_2')])
            ->shouldBeCalledOnce();

        $this
            ->getForKeys(['123', 'entity_code_2'], $this->queryToFetchEntitiesFromCodes($entityObjectQuery->getWrappedObject()))
            ->shouldBeLike([
                '123' => new EntityObject('123'),
                'entity_code_2' => new EntityObject('entity_code_2'),
            ]);
    }

    private function queryToFetchEntityFromCode(EntityObjectQuery $entityObjectQuery)
    {
        return function(string $entityCode)  use ($entityObjectQuery) {
            return $entityObjectQuery->fromCode($entityCode);
        };
    }

    private function queryToFetchEntitiesFromCodes(EntityObjectQuery $entityObjectQuery)
    {
        return function(array $entityCodes) use ($entityObjectQuery) {
            return $entityObjectQuery->fromCodes($entityCodes);
        };
    }
}

interface EntityObjectQuery {
    public function fromCode(string $entityCode): EntityObject;
    public function fromCodes(array $entityCodes): array;
}

class EntityObject {
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
