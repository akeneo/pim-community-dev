<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Attribute option normalizer for internal api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var array $supportedFormat */
    protected $supportedFormat = ['array'];

    /** @var array $activeLocales */
    protected $activeLocales;

    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    /** @var AttributeOptionManager $attributeOptionManager */
    protected $attributeOptionManager;

    /**
     * @param LocaleManager          $localeManager
     * @param AttributeOptionManager $attributeOptionManager
     */
    public function __construct(LocaleManager $localeManager, AttributeOptionManager $attributeOptionManager)
    {
        $this->localeManager          = $localeManager;
        $this->attributeOptionManager = $attributeOptionManager;
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
            'id'           => $object->getId(),
            'code'         => $object->getCode(),
            'optionValues' => $normalizedOptionValues
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOption && in_array($format, $this->supportedFormat);
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
        $activeLocales = $this->getActiveLocales();
        $usedLocales   = [];

        foreach ($optionValues as $optionValue) {
            $usedLocales[] = $optionValue->getLocale();
        }

        foreach ($activeLocales as $activatedLocale) {
            if (!in_array($activatedLocale->getCode(), $usedLocales)) {
                $attributeOptionValue = $this->attributeOptionManager->createAttributeOptionValue();
                $attributeOptionValue->setLocale($activatedLocale->getCode());
                $attributeOptionValue->setValue('');

                $optionValues->add($attributeOptionValue);
            }
        }

        return $optionValues;
    }

    /**
     * Get cached active locales
     *
     * @return array
     */
    protected function getActiveLocales()
    {
        if (!$this->activeLocales) {
            $this->activeLocales = $this->localeManager->getActiveLocales();
        }

        return $this->activeLocales;
    }
}
