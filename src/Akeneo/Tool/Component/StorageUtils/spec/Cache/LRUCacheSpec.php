<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\StorageUtils\Cursor;

use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LRUCacheSpec extends ObjectBehavior
{
    private const DEFAULT_VALUE = "a_default_value";

    public function let()
    {
        $this->beConstructedWith(4);
    }

    function it_cannot_be_instantiated_with_zero_or_negative_value()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [0]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('__construct', [-1]);
    }

    function it_gets_the_right_result()
    {
        $this->storeElements(4);
        $this->getOrElse("10", self::DEFAULT_VALUE)->shouldBeLike(new \Specification\Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeTypes\Sql\TestObject("10"));
        $this->getOrElse("112", self::DEFAULT_VALUE)->shouldBe(self::DEFAULT_VALUE);
    }

    function it_removes_the_least_recently_used_element_when_maximum_size_is_reached()
    {
        $this->storeElements(4);
        $this->put("112", new TestObject("112"));
        $this->getOrElse("10", self::DEFAULT_VALUE)->shouldBe(self::DEFAULT_VALUE);
    }

    function it_does_not_remove_the_least_recently_used_element_if_the_maximum_size_is_not_reached()
    {
        $this->storeElements(3);
        $this->put("112", new TestObject("1"));
        $this->getOrElse("10", self::DEFAULT_VALUE)->shouldNotBeNull();
    }

    function it_is_able_to_store_null_value()
    {
        $this->storeElements(2);
        $this->put("1123", null);
        $this->getOrElse("1123", self::DEFAULT_VALUE)->shouldBeNull();
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
