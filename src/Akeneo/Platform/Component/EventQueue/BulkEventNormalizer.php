<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkEventNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private EventNormalizer $eventNormalizer;

    public function __construct(EventNormalizer $eventNormalizer)
    {
        $this->eventNormalizer = $eventNormalizer;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof BulkEvent;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === BulkEvent::class;
    }

    /**
     * @param BulkEvent $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (false === $this->supportsNormalization($object, $format)) {
            throw new \InvalidArgumentException();
        }

        return array_map(function (Event $event) {
            return $this->eventNormalizer->normalize($event);
        }, $object->getEvents());
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (false === $this->supportsDenormalization($data, $type, $format)) {
            throw new \InvalidArgumentException();
        }

        if (!class_exists($type)) {
            throw new RuntimeException(sprintf('The class "%s" is not defined.', $type));
        }

        $events = array_map(function (array $eventData) {
            return $this->eventNormalizer->denormalize($eventData, $eventData['type']);
        }, $data);

        return new BulkEvent($events);
    }
}
