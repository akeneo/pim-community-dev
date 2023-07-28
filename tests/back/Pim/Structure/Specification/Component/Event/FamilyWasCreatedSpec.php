<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\FamilyWasCreated;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyWasCreatedSpec extends ObjectBehavior
{
    function it_can_be_normalized()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $this->beConstructedWith(5, 'the_code', $date);

        $this->normalize()->shouldReturn([
            'id' => 5,
            'code' => 'the_code',
            'created_at' => $date->format(\DateTimeInterface::ATOM),
        ]);
    }

    function it_can_be_constructed_from_normalized()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');

        $normalized = [
            'id' => 5,
            'code' => 'the_code',
            'created_at' => $date->format(\DateTimeInterface::ATOM),
        ];
        $this->beConstructedThrough('denormalize', [$normalized]);
        $this->shouldBeLike(new FamilyWasCreated(5, 'the_code', $date));
    }

    function it_cannot_be_constructed_without_id()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');

        $normalized = [
            'code' => 'code',
            'created_at' => '2020-11-24T22:02:12+00:00',
        ];
        $this->beConstructedThrough('denormalize', [$normalized]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_empty_code()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');

        $normalized = [
            'id' => 5,
            'code' => '',
            'created_at' => '2020-11-24T22:02:12+00:00',
        ];
        $this->beConstructedThrough('denormalize', [$normalized]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_wrong_date()
    {
        $normalized = [
            'id' => 12,
            'code' => 'code',
            'created_at' => 'bad format',
        ];
        $this->beConstructedThrough('denormalize', [$normalized]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
