<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Allows to compute raw values of the product template (that are in JSON in the
 * database) from the product values objects.
 *
 * This is not done directly in the group saver as it's only a technical problem.
 * The group saver only handles business stuff.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductTemplateRawValuesSubscriber implements EventSubscriberInterface
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
        $variantGroup = $event->getSubject();
        if (!$variantGroup instanceof GroupInterface || !$variantGroup->getType()->isVariant()) {
            return;
        }

        $productTemplate = $variantGroup->getProductTemplate();
        if (null === $productTemplate) {
            return;
        }

        $rawValues = $this->normalizer->normalize($productTemplate->getValues(), 'storage');

        $productTemplate->setValuesData($rawValues);
    }
}
