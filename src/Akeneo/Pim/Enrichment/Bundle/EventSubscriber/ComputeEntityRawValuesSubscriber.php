<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Allows to compute raw values of the product (that are in JSON in the database)
 * from the product values objects.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeEntityRawValuesSubscriber implements EventSubscriberInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'computeRawValues'];
    }

    /**
     * Normalizes product values into "storage" format, and sets the result as raw values.
     *
     * @param GenericEvent $event
     */
    public function computeRawValues(GenericEvent $event)
    {
        $subject = $event->getSubject();
        if (!$subject instanceof EntityWithValuesInterface) {
            return;
        }

        $values = $this->getValues($subject);
        $rawValues = $this->normalizer->normalize($values, 'storage');
        $subject->setRawValues($rawValues);
    }

    /**
     * For products and product models we want to retrieve only the values of the current variation.
     *
     * @param EntityWithValuesInterface $entity
     *
     * @return ValueCollectionInterface
     */
    private function getValues(EntityWithValuesInterface $entity): ValueCollectionInterface
    {
        if ($entity instanceof ProductModelInterface) {
            return $entity->getValuesForVariation();
        }

        if ($entity instanceof ProductInterface) {
            return $entity->getValuesForVariation();
        }

        return $entity->getValues();
    }
}
