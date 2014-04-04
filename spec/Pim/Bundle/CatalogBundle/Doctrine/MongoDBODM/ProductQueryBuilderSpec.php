<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(CatalogContext $context)
    {
        $context->getLocaleCode()->willReturn('en_US');
        $context->getScopeCode()->willReturn('mobile');
        $this->beConstructedWith($context);
    }

    function it_throws_a_logic_exception_when_query_builder_is_not_configured()
    {
        $exception = new \LogicException('Query builder must be configured');
        $this->shouldThrow($exception)->duringAddFieldFilter('field', '=', 'value');
    }
}
