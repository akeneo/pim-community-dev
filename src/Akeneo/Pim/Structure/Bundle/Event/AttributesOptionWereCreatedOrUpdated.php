<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Event;

use Traversable;
use Webmozart\Assert\Assert;

final class AttributesOptionWereCreatedOrUpdated implements \IteratorAggregate
{
    /**
     * @param (AttributeOptionWasCreated|AttributeOptionWasUpdated)[] $events
     */
    public function __construct(
        public readonly array $events,
    ) {
        Assert::notEmpty($this->events);
        Assert::allIsInstanceOfAny($this->events, [AttributeOptionWasCreated::class, AttributeOptionWasUpdated::class]);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * @return array<string, array<int, array>>
     */
    public function normalize(): array
    {
        return [
            'events' => \array_map(
                static fn (AttributeOptionWasCreated|AttributeOptionWasUpdated $attributeOptionWasCreatedOrUpdated): array => $attributeOptionWasCreatedOrUpdated->normalize(),
                $this->events
            ),
        ];
    }

    /**
     * @param array<string, array<int, array<mixed>>> $normalized
     */
    public static function denormalize(array $normalized): self
    {
        Assert::keyExists($normalized, 'events');
        Assert::allIsArray($normalized['events']);

        return new AttributesOptionWereCreatedOrUpdated(
            \array_map(
                static function (array $eventNormalized): AttributeOptionWasCreated|AttributeOptionWasUpdated {
                    if (\array_key_exists('created_at', $eventNormalized)) {
                        return AttributeOptionWasCreated::denormalize($eventNormalized);
                    }

                    if (\array_key_exists('updated_at', $eventNormalized)) {
                        return AttributeOptionWasUpdated::denormalize($eventNormalized);
                    }

                    throw new \InvalidArgumentException('Normalized event should have a "created_at" or "updated_at" key');
                },
                $normalized['events']
            )
        );
    }

    public function getOlderEventDate(): \DateTimeImmutable
    {
        $attributeWasCreatedOrUpdated = current($this->events);
        Assert::notNull($attributeWasCreatedOrUpdated);
        $minDate = $attributeWasCreatedOrUpdated instanceof AttributeOptionWasCreated
            ? $attributeWasCreatedOrUpdated->createdAt
            : $attributeWasCreatedOrUpdated->updatedAt
        ;

        foreach ($this->events as $attributeWasCreatedOrUpdated) {
            $date = $attributeWasCreatedOrUpdated instanceof AttributeOptionWasCreated
                ? $attributeWasCreatedOrUpdated->createdAt
                : $attributeWasCreatedOrUpdated->updatedAt
            ;
            if (null === $minDate || $minDate > $date) {
                $minDate = $date;
            }
        }

        return $minDate;
    }
}
