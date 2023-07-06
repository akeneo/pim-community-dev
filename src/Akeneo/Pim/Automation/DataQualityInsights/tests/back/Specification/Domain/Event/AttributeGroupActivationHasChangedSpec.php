<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Event;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupActivationHasChangedSpec extends ObjectBehavior
{
    function it_can_be_normalized()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $this->beConstructedWith('the_code', false, $date);

        $this->normalize()->shouldReturn([
            'attribute_group_code' => 'the_code',
            'new_is_activated' => false,
            'updated_at' => '2020-11-24T22:02:12+00:00',
        ]);
    }

    function it_can_be_denormalized()
    {
        $this->beConstructedWith('the_code', false, new \DateTimeImmutable());

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
            'attribute_group_code' => 'the_code',
            'new_is_activated' => true,
            'updated_at' => '2020-11-24T22:02:12+00:00',
        ];

        $this->denormalize($normalized)->shouldBeLike(new AttributeGroupActivationHasChanged('the_code', true, $date));
    }

    function it_cannot_be_denormalized_with_wrong_code()
    {
        $this->beConstructedWith('the_code', false, new \DateTimeImmutable());

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
            'attribute_group_code' => 12,
            'new_is_activated' => true,
            'updated_at' => '2020-11-24T22:02:12+00:00',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$normalized]);
    }

    function it_cannot_be_denormalized_with_wrong_value()
    {
        $this->beConstructedWith('the_code', false, new \DateTimeImmutable());

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
            'attribute_group_code' => 'the_code',
            'new_is_activated' => 'true',
            'updated_at' => '2020-11-24T22:02:12+00:00',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$normalized]);
    }

    function it_cannot_be_denormalized_with_wrong_date()
    {
        $this->beConstructedWith('the_code', false, new \DateTimeImmutable());

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $normalized = [
            'attribute_group_code' => 'the_code',
            'new_is_activated' => true,
            'updated_at' => 'wrong date',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$normalized]);
    }
}
