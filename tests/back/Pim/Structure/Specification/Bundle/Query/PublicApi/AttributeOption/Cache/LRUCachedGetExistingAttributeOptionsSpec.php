<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache\LRUCachedGetExistingAttributeOptions;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Tool\Component\StorageUtils\Cache\LRUCache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LRUCachedGetExistingAttributeOptionsSpec extends ObjectBehavior
{
    function let(GetExistingAttributeOptionCodes $sqlQuery)
    {
        $this->beConstructedWith($sqlQuery, new LRUCache(4));
    }

    function it_is_a_get_existing_attribute_options_query()
    {
        $this->shouldImplement(GetExistingAttributeOptionCodes::class);
    }

    function it_is_a_cached_version_of_the_query()
    {
        $this->shouldHaveType(LRUCachedGetExistingAttributeOptions::class);
    }

    function it_gets_existing_options_by_performing_an_sql_query_if_the_cache_is_not_hit(
        GetExistingAttributeOptionCodes $sqlQuery
    ) {
        $sqlQuery->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
                 ->shouldBeCalledOnce()
                 ->willReturn(['attribute_1' => ['option2']]);

        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
             ->shouldReturn(['attribute_1' => ['option2']]);
    }

    function it_does_not_perform_an_sql_query_when_the_cache_is_hit(GetExistingAttributeOptionCodes $sqlQuery)
    {
        $sqlQuery->fromOptionCodesByAttributeCode(Argument::type('array'))
                 ->shouldBeCalledOnce()
                 ->willReturn(['attribute_1' => ['option2', 'option3']]);

        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2', 'option3', 'option4']])
             ->shouldReturn(['attribute_1' => ['option2', 'option3']]);
        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option4', 'option3', 'option1', 'option2']])
            ->shouldReturn(['attribute_1' => ['option3', 'option2']]);
    }

    function it_mixes_calls_between_the_cached_and_the_non_cached(GetExistingAttributeOptionCodes $sqlQuery)
    {
        $sqlQuery->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
                 ->shouldBeCalledOnce()
                 ->willReturn(['attribute_1' => ['option2']]);
        $sqlQuery->fromOptionCodesByAttributeCode(['attribute_1' => ['option3'], 'attribute_2' => ['other_option']])
                 ->shouldBeCalledOnce()
                 ->willReturn(['attribute_1' => ['option3'], 'attribute_2' => ['other_option']]);

        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
             ->shouldReturn(['attribute_1' => ['option2']]);
        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2', 'option3'], 'attribute_2' => ['other_option']])
             ->shouldReturn([
                 'attribute_1' => ['option3', 'option2'],
                 'attribute_2' => ['other_option'],
             ]);
    }

    function it_uses_the_cache_with_case_insensitive(GetExistingAttributeOptionCodes $sqlQuery)
    {
        $sqlQuery->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
                 ->shouldBeCalledOnce()
                 ->willReturn(['attribute_1' => ['option1']]);

        // The first time, the cache is set.
        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['OPTION1', 'option2']])
             ->shouldReturn(['attribute_1' => ['option1']]);
        // Nex times, the cache is used.
        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['OPTION1', 'option2']])
            ->shouldReturn(['attribute_1' => ['option1']]);
        $this->fromOptionCodesByAttributeCode(['attribute_1' => ['option1', 'option2']])
            ->shouldReturn(['attribute_1' => ['option1']]);
    }
}
