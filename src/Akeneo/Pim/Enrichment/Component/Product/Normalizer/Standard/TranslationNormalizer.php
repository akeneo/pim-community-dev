<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface|null $localeRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $localeRepository = null)
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
        $method = sprintf('get%s', ucfirst($context['property']));

        foreach ($object->getTranslations() as $translation) {
            $locale = $this->localeRepository->findOneByIdentifier($translation->getLocale());
            if (null === $locale || !$locale->isActivated()) {
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
