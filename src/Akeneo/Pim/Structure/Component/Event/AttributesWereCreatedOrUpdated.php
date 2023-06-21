<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdated implements \IteratorAggregate
{
    /**
     * @param (AttributeWasCreated|AttributeWasUpdated)[] $events
     */
    public function __construct(public readonly array $events)
    {
        Assert::notEmpty($events);
        Assert::allIsInstanceOfAny($events, [AttributeWasCreated::class, AttributeWasUpdated::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * @return array<string, array<int, array<mixed>>>
     */
    public function normalize(): array
    {
        return [
            'events' => \array_map(
                static fn (AttributeWasCreated|AttributeWasUpdated $attributeWasCreatedOrUpdated): array => $attributeWasCreatedOrUpdated->normalize(),
                $this->events
            ),
        ];
    }

    /**
     * @param array<string, array<int, array<mixed>>> $normalized
     */
    public static function denormalize(array $normalized): AttributesWereCreatedOrUpdated
    {
        Assert::keyExists($normalized, 'events');
        Assert::allIsArray($normalized['events']);

        return new AttributesWereCreatedOrUpdated(
            \array_map(
                function (array $eventNormalized): AttributeWasCreated|AttributeWasUpdated {
                    if (\array_key_exists('created_at', $eventNormalized)) {
                        return AttributeWasCreated::denormalize($eventNormalized);
                    } elseif (\array_key_exists('updated_at', $eventNormalized)) {
                        return AttributeWasUpdated::denormalize($eventNormalized);
                    } else {
                        throw new \InvalidArgumentException('Normalized event should have a "created_at" or "updated_at" key');
                    }
                },
                $normalized['events']
            )
        );
    }

    public function getOlderEventDate(): \DateTimeImmutable
    {
        $attributeWasCreatedOrUpdated = current($this->events);
        Assert::notNull($attributeWasCreatedOrUpdated);
        $minDate = $attributeWasCreatedOrUpdated instanceof AttributeWasCreated
            ? $attributeWasCreatedOrUpdated->createdAt
            : $attributeWasCreatedOrUpdated->updatedAt
        ;

        foreach ($this->events as $attributeWasCreatedOrUpdated) {
            $date = $attributeWasCreatedOrUpdated instanceof AttributeWasCreated
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
