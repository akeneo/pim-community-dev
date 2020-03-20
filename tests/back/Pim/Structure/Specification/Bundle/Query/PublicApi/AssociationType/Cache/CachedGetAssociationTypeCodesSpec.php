<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\AssociationType\Cache;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AssociationType\Cache\CachedGetAssociationTypeCodes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AssociationType\GetAssociationTypeCodes;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class CachedGetAssociationTypeCodesSpec extends ObjectBehavior
{
    function let(GetAssociationTypeCodes $getAssociationTypeCodes)
    {
        $this->beConstructedWith($getAssociationTypeCodes);
    }

    function it_is_initilizable()
    {
        $this->shouldBeAnInstanceOf(CachedGetAssociationTypeCodes::class);
    }

    function it_returns_all_the_codes_without_cache(GetAssociationTypeCodes $getAssociationTypeCodes)
    {
        $codes = ['a', 'b', 'c'];
        $getAssociationTypeCodes->findAll()->willReturn(new \ArrayIterator($codes));

        $results = $this->findAll();
        $results->shouldBeAnInstanceOf(\Iterator::class);

        $array = iterator_to_array($results->getWrappedObject());
        Assert::eq($array, $codes);
    }

    function it_returns_all_the_codes_with_cache(GetAssociationTypeCodes $getAssociationTypeCodes)
    {
        $codes = ['a', 'b', 'c'];

        $getAssociationTypeCodes->findAll()->willReturn(new \ArrayIterator($codes));
        $getAssociationTypeCodes->findAll()->shouldBeCalledOnce();

        $results = $this->findAll();
        $results->shouldBeAnInstanceOf(\Iterator::class);
        Assert::eq(iterator_to_array($results->getWrappedObject()), $codes);

        $results = $this->findAll();
        $results->shouldBeAnInstanceOf(\Iterator::class);
        Assert::eq(iterator_to_array($results->getWrappedObject()), $codes);
    }

    function it_returns_all_the_codes_when_no_cache_can_be_set(GetAssociationTypeCodes $getAssociationTypeCodes)
    {
        $codes = array_fill(0, 99999, 'a');

        $getAssociationTypeCodes->findAll()->willReturn(new \ArrayIterator($codes));
        $getAssociationTypeCodes->findAll()->shouldBeCalledTimes(2);

        $results = $this->findAll();
        $results->shouldBeAnInstanceOf(\Iterator::class);
        Assert::eq(iterator_to_array($results->getWrappedObject()), $codes);

        $results = $this->findAll();
        $results->shouldBeAnInstanceOf(\Iterator::class);
        Assert::eq(iterator_to_array($results->getWrappedObject()), $codes);
    }
}
