<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class ProductUuidSpec extends ObjectBehavior
{
    public function it_can_be_constructed_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['6d125b99-d971-41d9-a264-b020cd486aee']);
        $this->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toBytes()->shouldReturn(Uuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee')->getBytes());
        $this->__toString()->shouldReturn('6d125b99-d971-41d9-a264-b020cd486aee');
    }

    public function it_can_be_constructed_from_a_uuid()
    {
        $uuid = Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');

        $this->beConstructedThrough('fromUuid', [$uuid]);
        $this->shouldBeAnInstanceOf(ProductUuid::class);
        $this->toBytes()->shouldReturn($uuid->getBytes());
        $this->__toString()->shouldReturn('df470d52-7723-4890-85a0-e79be625e2ed');
    }

    public function it_throws_an_exception_if_the_product_uuid_is_not_string_when_using_fromString()
    {
        $this->beConstructedThrough('fromString', [12]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
