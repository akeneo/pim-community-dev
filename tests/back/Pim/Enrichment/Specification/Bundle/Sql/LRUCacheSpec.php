<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Sql;

use Akeneo\Pim\Enrichment\Bundle\Sql\LRUCache;
use PhpSpec\ObjectBehavior;

class LRUCacheSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith(4);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LRUCache::class);
    }

    function it_cannot_be_instantiated_with_zero_or_negative_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [0]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [-1]);
    }

    function it_gets_the_right_result() {
        $this->storeElements(4);
        $this->get("10")->shouldBeLike(new TestObject("10"));
        $this->get("112")->shouldBe(null);
    }

    function it_stores_elements() {
        $testObject = new TestObject("112");

        $this->get("112")->shouldReturn(null);
        $this->put("112", $testObject);
        $this->get("112")->shouldReturn($testObject);
    }

    function it_removes_the_least_recently_used_element_when_maximum_size_is_reached()
    {
        $this->storeElements(4);
        $this->put("112", new TestObject(112));
        $this->get("10")->shouldBeNull();
    }

    function it_does_not_remove_the_least_recently_used_element_if_the_maximum_size_is_not_reached()
    {
        $this->storeElements(3);
        $this->put("112", new TestObject(1));
        $this->get("10")->shouldNotBeNull();
    }

    private function storeElements(int $numberOfElements)
    {
        for ($i = 10; $i <= $numberOfElements + 9; $i++) {
            $this->put($i, new TestObject("$i"));
        }
    }
}

class TestObject {
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
