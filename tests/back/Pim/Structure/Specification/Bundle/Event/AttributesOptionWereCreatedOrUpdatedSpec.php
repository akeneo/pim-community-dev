<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Pim\Structure\Bundle\Event;

use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasCreated;
use Akeneo\Pim\Structure\Bundle\Event\AttributeOptionWasUpdated;
use Akeneo\Pim\Structure\Bundle\Event\AttributesOptionWereCreatedOrUpdated;
use PhpSpec\ObjectBehavior;

final class AttributesOptionWereCreatedOrUpdatedSpec extends ObjectBehavior
{
    public function it_is_traversable(): void
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeOptionWasCreated(1, 'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]);

        $this->shouldImplement(\Traversable::class);
    }

    public function it_cannot_be_constructed_with_wrong_item(): void
    {
        $this->beConstructedWith([
            new AttributeOptionWasCreated(1,'color', 'name',  new \DateTimeImmutable()),
            new \stdClass(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_normalized(): void
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeOptionWasCreated(1,'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]);

        $this->normalize()->shouldReturn(['events' => [
            [
                'id' => 1,
                'code' => 'color',
                'attribute_code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'size',
                'attribute_code' => 'name',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]]);
    }

    public function it_can_be_denormalized(): void
    {
        // Fake construct is needed for phpspec even if we don't use it
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([new AttributeOptionWasCreated(1, 'color', 'name', $date)]);

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $denormalize = ['events' => [
            [
                'id' => 1,
                'code' => 'color',
                'attribute_code' => 'name',
                'created_at' => $date->format(\DateTimeInterface::ATOM),
            ],
            [
                'id' => 2,
                'code' => 'size',
                'attribute_code' => 'name',
                'updated_at' => $date->format(\DateTimeInterface::ATOM),
            ],
        ]];

        $this::denormalize($denormalize)->shouldBeLike(new AttributesOptionWereCreatedOrUpdated([
            new AttributeOptionWasCreated(1, 'color', 'name', $date),
            new AttributeOptionWasUpdated(2, 'size', 'name', $date),
        ]));
    }

    public function it_returns_the_older_date(): void
    {
        $date1 = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $date2 = new \DateTimeImmutable('2020-11-22T22:02:12+00:00');
        $date3 = new \DateTimeImmutable('2020-11-28T22:02:12+00:00');
        $this->beConstructedWith([
            new AttributeOptionWasCreated(1, 'color', 'name', $date1),
            new AttributeOptionWasUpdated(2, 'color', 'name', $date2),
            new AttributeOptionWasCreated(3, 'color', 'name', $date3),
        ]);

        $this->getOlderEventDate()->shouldReturn($date2);
    }
}
