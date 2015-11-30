<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
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

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeOptionManager */
    protected $optionManager;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param AttributeOptionManager    $optionManager
     */
    public function __construct(LocaleRepositoryInterface $localeRepository, AttributeOptionManager $optionManager)
    {
        $this->localeRepository = $localeRepository;
        $this->optionManager    = $optionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
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
            $this->activeLocales = $this->localeRepository->getActivatedLocales();
        }

        return $this->activeLocales;
    }
}
