<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasUpdated;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedSpec extends ObjectBehavior
{
    function it_is_traversable()
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]);

        $this->shouldImplement(\Traversable::class);
    }

    function it_cannot_be_constructed_with_wrong_item()
    {
        $this->beConstructedWith([
            new AttributeWasCreated(1, 'name', new \DateTimeImmutable()),
            new \stdClass(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]);

        $this->normalize()->shouldReturn(['events' => [
            [
                'id' => 1,
                'code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]]);
    }

    function it_can_be_denormalized()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([new AttributeWasCreated(1, 'name', $date)]);

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $denormalize = ['events' => [
            [
                'id' => 1,
                'code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]];

        $this::denormalize($denormalize)->shouldBeLike(new AttributesWereCreatedOrUpdated([
            new AttributeWasCreated(1, 'name', $date),
            new AttributeWasUpdated(2, 'desc', $date),
        ]));
    }

    function it_returns_the_older_date()
    {
        $date1 = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $date2 = new \DateTimeImmutable('2020-11-22T22:02:12+00:00');
        $date3 = new \DateTimeImmutable('2020-11-28T22:02:12+00:00');
        $this->beConstructedWith([
            new AttributeWasCreated(1, 'name', $date1),
            new AttributeWasUpdated(2, 'desc', $date2),
            new AttributeWasCreated(3, 'author', $date3),
        ]);

        $this->getOlderEventDate()->shouldReturn($date2);
    }
}
