<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface
{
    /** @var LocaleRepositoryInterface */
    private $localeRepository;
    private $activeLocales = [];

    public function __construct(LocaleRepositoryInterface $localeRepository = null)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context = array_merge(
            [
                'property' => 'label',
                'locales'  => [],
            ],
            $context
        );

        $translations = array_fill_keys($context['locales'], null);

        // TODO merge: remove null in master
        if (null !== $this->localeRepository) {
            $this->activeLocales = $this->activeLocales ?: $this->localeRepository->getActivatedLocaleCodes();
            $translations = array_combine($this->activeLocales, array_fill(0, count($this->activeLocales), null));
        }

        $method = sprintf('get%s', ucfirst($context['property']));

        foreach ($object->getTranslations() as $translation) {
            if (!in_array($translation->getLocale(), $this->activeLocales)) {
                continue;
            }

            if (false === method_exists($translation, $method)) {
                throw new \LogicException(
                    sprintf("Class %s doesn't provide method %s", get_class($translation), $method)
                );
            }

            if (empty($context['locales']) || in_array($translation->getLocale(), $context['locales'])) {
                $translations[$translation->getLocale()] = '' === $translation->$method() ?
                    null : $translation->$method();
            }
        }

        return $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TranslatableInterface && 'standard' === $format;
    }
}
