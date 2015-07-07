<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
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
    /** @var array */
    protected $supportedFormat = ['array'];

    /** @var array */
    protected $activeLocales;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var LocaleManager */
    protected $localeManager;

    /** @var AttributeOptionManager */
    protected $optionManager;

    /**
     * @param LocaleManager          $localeManager
     * @param AttributeOptionManager $optionManager
     */
    public function __construct(LocaleManager $localeManager, AttributeOptionManager $optionManager)
    {
        $this->localeManager = $localeManager;
        $this->optionManager = $optionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $optionsValues = $context['onlyActivatedLocales'] ?
            $this->ensureEmptyOptionValues($object->getOptionValues()) :
            $object->getOptionValues();

        $normalizedValues = $this->serializer->normalize($optionsValues, $format, $context);

        return [
            'id'           => $object->getId(),
            'code'         => $object->getCode(),
            'optionValues' => $normalizedValues
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && in_array($format, $this->supportedFormat);
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
                $attributeOptionValue = $this->optionManager->createAttributeOptionValue();
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
