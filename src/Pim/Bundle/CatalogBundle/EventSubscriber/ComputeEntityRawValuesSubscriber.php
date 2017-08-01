<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param NormalizerInterface          $normalizer
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(NormalizerInterface $normalizer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->normalizer = $normalizer;
        $this->attributeRepository = $attributeRepository;
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

        $values = $subject->getValues();
        if ($subject instanceof EntityWithFamilyVariantInterface) {
            $values = $this->removeAncestryValues($subject);
        }

        $rawValues = $this->normalizer->normalize($values, 'storage');
        $subject->setRawValues($rawValues);
    }

    /**
     * Remove all the parent values of an entity.
     * Here we copy the values here so that we don't touch the initial values collection.
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return ValueCollectionInterface
     */
    private function removeAncestryValues(EntityWithFamilyVariantInterface $entity): ValueCollectionInterface
    {
        $values = ValueCollection::fromCollection($entity->getValues());

        $ancestryAttributeCodes = array_keys($this->getAncestryRawValues($entity));
        foreach ($values as $value) {
            if (in_array($value->getAttribute()->getCode(), $ancestryAttributeCodes)) {
                $values->remove($value);
            }
        }

        return $values;
    }

    /**
     * Recursively get  the raw values of all the parents of an entity.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @param array                            $ancestryRawValues
     *
     * @return array
     */
    private function getAncestryRawValues(EntityWithFamilyVariantInterface $entity, array $ancestryRawValues = []): array
    {
        $parent = $entity->getParent();

        if (null === $parent) {
            return $ancestryRawValues;
        }

        return $this->getAncestryRawValues($parent, array_merge($ancestryRawValues, $parent->getRawValues()));
    }
}
