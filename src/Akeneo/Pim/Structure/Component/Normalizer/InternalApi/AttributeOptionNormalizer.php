<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
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
class AttributeOptionNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormat = ['array'];

    /** @var array */
    protected $activeLocales;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var SimpleFactoryInterface */
    protected $optionValueFactory;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param SimpleFactoryInterface    $optionValueFactory
     */
    public function __construct(LocaleRepositoryInterface $localeRepository, SimpleFactoryInterface $optionValueFactory)
    {
        $this->localeRepository = $localeRepository;
        $this->optionValueFactory = $optionValueFactory;
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
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
        $usedLocales = [];

        foreach ($optionValues as $optionValue) {
            $usedLocales[] = $optionValue->getLocale();
        }

        foreach ($activeLocales as $activatedLocale) {
            if (!in_array($activatedLocale->getCode(), $usedLocales)) {
                $attributeOptionValue = $this->optionValueFactory->create();
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
