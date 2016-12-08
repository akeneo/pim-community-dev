<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\normalizer\Normalizer\NormalizerInterface;

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
class ComputeProductRawValuesSubscriber implements EventSubscriberInterface
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
     * Normalizes product values into "storage" format,
     * and sets the result as raw values.
     *
     * @param GenericEvent $event
     */
    public function computeRawValues(GenericEvent $event)
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface) {
            return;
        }

        $values = $this->normalizer->normalize($product->getValues(), 'storage');
        $product->setRawValues($values);
    }
}
