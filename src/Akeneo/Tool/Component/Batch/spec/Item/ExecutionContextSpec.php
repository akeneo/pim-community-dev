<?php

namespace spec\Akeneo\Tool\Component\Batch\Item;

use PhpSpec\ObjectBehavior;

class ExecutionContextSpec extends ObjectBehavior
{
    function it_is_dirty()
    {
        $this->isDirty()->shouldReturn(false);
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
    }

    function it_allows_to_change_dirty_flag()
    {
        $this->isDirty()->shouldReturn(false);
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
        $this->clearDirtyFlag();
        $this->isDirty()->shouldReturn(false);
    }

    function it_allows_to_add_value()
    {
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
        $this->get('test_key')->shouldReturn('test_value');
    }

    function it_allows_to_remove_value()
    {
        $this->put('test_key', 'test_value');
        $this->get('test_key')->shouldReturn('test_value');
        $this->remove('test_key');
        $this->get('test_key')->shouldReturn(null);
    }

    function it_provides_keys()
    {
        $this->put('test_key', 'test_value');
        $this->put('test_key2', 'test_value');
        $this->getKeys()->shouldReturn(['test_key', 'test_key2']);
    }
}
