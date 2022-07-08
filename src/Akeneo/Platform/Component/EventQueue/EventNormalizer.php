<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Event;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return is_subclass_of($type, Event::class);
    }

    /**
     * @param Event $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        if (false === $this->supportsNormalization($object, $format)) {
            throw new \InvalidArgumentException();
        }

        return [
            'type' => \get_class($object),
            'name' => $object->getName(),
            'author' => $object->getAuthor()->name(),
            'author_type' => $object->getAuthor()->type(),
            'data' => $object->getData(),
            'timestamp' => $object->getTimestamp(),
            'uuid' => $object->getUuid(),
        ];
    }

    public function denormalize($data, $type, $format = null, array $context = []): Event
    {
        if (false === $this->supportsDenormalization($data, $type, $format)) {
            throw new \InvalidArgumentException();
        }

        if (!class_exists($type)) {
            throw new RuntimeException(sprintf('The class "%s" is not defined.', $type));
        }

        $eventData = $data['data'];
        if (array_key_exists('uuid', $eventData)) {
            $eventData = array_merge($eventData, ['uuid' => Uuid::fromString($eventData['uuid'])]);
        }
        // /!\ Do not change to a new format for event without a strategy to
        // support the previous/old format of the events already in the queue (before the migration).
        return new $type(
            Author::fromNameAndType($data['author'], $data['author_type'] ?? Author::TYPE_API),
            $eventData,
            $data['timestamp'],
            $data['uuid']
        );
    }
}
