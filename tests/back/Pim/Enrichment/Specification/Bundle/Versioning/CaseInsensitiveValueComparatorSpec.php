<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Versioning;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeCodes;
use PhpSpec\ObjectBehavior;

class CaseInsensitiveValueComparatorSpec extends ObjectBehavior
{
    function let(GetAttributeCodes $getAttributeCodes)
    {
        $getAttributeCodes->forAttributeTypes(['type1', 'type2'])->willReturn(['code1', 'code2']);

        $this->beConstructedWith($getAttributeCodes, ['type1', 'type2']);
    }

    function it_supports_fields()
    {
        $this->supportsField('code1')->shouldReturn(true);
        $this->supportsField('code2')->shouldReturn(true);
        $this->supportsField('code3')->shouldReturn(false);
    }

    function it_equals_another_value_with_case_insensitive()
    {
        $this->isEqual('test', 'test')->shouldReturn(true);
        $this->isEqual('test', 'tESt')->shouldReturn(true);
        $this->isEqual('teST', 'tESt')->shouldReturn(true);
        $this->isEqual('teST', 'tESt2')->shouldReturn(false);
        $this->isEqual(1, 1)->shouldReturn(true);
        $this->isEqual(1, 2)->shouldReturn(false);
    }
}
