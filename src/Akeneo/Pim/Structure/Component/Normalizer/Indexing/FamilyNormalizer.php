<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\Indexing;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\ValueCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexing normalizer for a product family.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var string[] */
    protected $activatedLocaleCodes;

    /**
     * @param NormalizerInterface       $translationNormalizer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        NormalizerInterface $translationNormalizer,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (null === $this->activatedLocaleCodes) {
            $this->activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();
        }

        $context = array_merge($context, [
            'locales' => $this->activatedLocaleCodes,
        ]);

        return [
            'code'   => $object->getCode(),
            'labels' => $this->translationNormalizer->normalize($object, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return (
                ValueCollectionNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format
            ) && $data instanceof FamilyInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
