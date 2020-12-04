<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Channel\Component\Query\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Adds default values (defined at attribute level) for products and product models
 */
class AddDefaultValuesSubscriber implements EventSubscriberInterface
{
    private GetAttributes $getAttributes;
    private ValueFactory $valueFactory;
    private GetChannelCodeWithLocaleCodesInterface $getChannelWithLocales;

    private ?array $cachedChannelsAndLocales = null;

    public function __construct(
        GetAttributes $getAttributes,
        ValueFactory $valueFactory,
        GetChannelCodeWithLocaleCodesInterface $getChannelWithLocales
    ) {
        $this->getAttributes = $getAttributes;
        $this->valueFactory = $valueFactory;
        $this->getChannelWithLocales = $getChannelWithLocales;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => ['addDefaultValues', 100],
        ];
    }

    public function addDefaultValues(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!$entity instanceof EntityWithFamilyInterface) {
            return;
        }
        if (!$event->hasArgument('is_new') || true !== $event->getArgument('is_new')) {
            return;
        }
        if ($event->hasArgument('add_default_values') && false === $event->getArgument('add_default_values')) {
            return;
        }

        $attributesWithDefaultValue = $this->getAttributesWithDefaultValues($entity);
        foreach ($attributesWithDefaultValue as $attribute) {
            $this->addValues($entity, $attribute);
        }
    }

    /**
     * Returns all the attributes with a default_value property for a given entity:
     * - for a simple product, the attribute must be part of the family
     * - for a variant product or product model, the attribute must be part of the matching variant attribute set
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return Attribute[]
     */
    private function getAttributesWithDefaultValues(EntityWithFamilyVariantInterface $entity): array
    {
        $attributes = new ArrayCollection();
        if (null !== $entity->getFamilyVariant()) {
            $level = $entity->getVariationLevel();
            $attributes = 0 === $level ?
                $entity->getFamilyVariant()->getCommonAttributes() :
                $entity->getFamilyVariant()->getVariantAttributeSet($level)->getAttributes();
        } elseif (null !== $entity->getFamily()) {
            $attributes = $entity->getFamily()->getAttributes();
        }

        $attributeCodesWithDefaultValues = $attributes->filter(
            fn (AttributeInterface $attribute): bool => null !== $attribute->getProperty('default_value')
        )->map(
            fn (AttributeInterface $attribute): string => $attribute->getCode()
        )->toArray();

        return empty($attributeCodesWithDefaultValues) ?
            [] :
            $this->getAttributes->forCodes($attributeCodesWithDefaultValues);
    }

    private function addValues(EntityWithFamilyVariantInterface $entity, Attribute $attribute): void
    {
        $defaultValue = $attribute->properties()['default_value'];

        if ($attribute->isScopable() && $attribute->isLocalizable()) {
            foreach ($this->getChannelCodesWithLocaleCodes() as $channelCode => $localeCodes) {
                foreach ($localeCodes as $localeCode) {
                    if ($attribute->isLocaleSpecific() && !\in_array($localeCode, $attribute->availableLocaleCodes())) {
                        continue;
                    }
                    $entity->addValue(
                        $this->valueFactory->createByCheckingData($attribute, $channelCode, $localeCode, $defaultValue)
                    );
                }
            }
        } elseif ($attribute->isScopable()) {
            foreach ($this->getChannelCodes() as $channelCode) {
                $entity->addValue(
                    $this->valueFactory->createByCheckingData($attribute, $channelCode, null, $defaultValue)
                );
            }
        } elseif ($attribute->isLocalizable()) {
            foreach ($this->getAllActiveLocaleCodes() as $localeCode) {
                if ($attribute->isLocaleSpecific() && !\in_array($localeCode, $attribute->availableLocaleCodes())) {
                    continue;
                }
                $entity->addValue(
                    $this->valueFactory->createByCheckingData($attribute, null, $localeCode, $defaultValue)
                );
            }
        } else {
            $entity->addValue($this->valueFactory->createByCheckingData($attribute, null, null, $defaultValue));
        }
    }

    private function getChannelCodesWithLocaleCodes(): array
    {
        $this->initializeChannelsAndLocales();

        return $this->cachedChannelsAndLocales;
    }

    private function getAllActiveLocaleCodes(): array
    {
        $this->initializeChannelsAndLocales();

        return array_values(array_unique(array_merge(...array_values($this->cachedChannelsAndLocales))));
    }

    private function getChannelCodes(): array
    {
        $this->initializeChannelsAndLocales();

        return array_keys($this->cachedChannelsAndLocales);
    }

    private function initializeChannelsAndLocales(): void
    {
        if (null === $this->cachedChannelsAndLocales) {
            $this->cachedChannelsAndLocales = [];
            $channelsAndLocales = $this->getChannelWithLocales->findAll();
            foreach ($channelsAndLocales as $item) {
                $this->cachedChannelsAndLocales[$item['channelCode']] = $item['localeCodes'];
            }
        }
    }
}
