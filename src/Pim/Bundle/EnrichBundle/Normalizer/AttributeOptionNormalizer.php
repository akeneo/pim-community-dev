<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute option normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    /** @var AttributeManager $attributeManager */
    protected $attributeManager;

    /**
     * @param LocaleManager    $localeManager
     * @param AttributeManager $attributeManager
     */
    public function __construct(LocaleManager $localeManager, AttributeManager $attributeManager)
    {
        $this->localeManager    = $localeManager;
        $this->attributeManager = $attributeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $optionsValues = $context['onlyActivatedLocales'] ?
            $this->ensureEmptyOptionValues($object->getOptionValues()) :
            $object->getOptionValues();

        $normalizedOptionValues = $this->serializer->normalize($optionsValues, $format, $context);

        return [
            'id'            => $object->getId(),
            'code'          => $object->getCode(),
            'default'       => $object->isDefault(),
            'translatable'  => $object->isTranslatable(),
            'sort_order'    => $object->getSortOrder(),
            'optionValues'  => $normalizedOptionValues
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOption && $format === 'array';
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Ensure that there is an option for all activated locales
     *
     * @param Collection $optionValues
     *
     * @return Collection
     */
    protected function ensureEmptyOptionValues(Collection $optionValues)
    {
        $activeLocales = $this->localeManager->getActiveLocales();
        $usedLocales   = [];

        foreach ($optionValues as $optionValue) {
            $usedLocales[] = $optionValue->getLocale();
        }

        foreach ($activeLocales as $activatedLocale) {
            if (!in_array($activatedLocale->getCode(), $usedLocales)) {
                $attributeOptionValue = $this->attributeManager->createAttributeOptionValue();
                $attributeOptionValue->setLocale($activatedLocale->getCode());
                $attributeOptionValue->setValue('');

                $optionValues->add($attributeOptionValue);
            }
        }

        return $optionValues;
    }
}
