<?php

namespace Pim\Component\Catalog\Normalizer\Indexing;

use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndProductModel;
use Pim\Component\Catalog\Normalizer\Indexing\ProductModel;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexing normalizer for a product family.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var LocaleInterface[] */
    protected $activatedLocales;

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
        if (null === $this->activatedLocales) {
            $this->activatedLocales = $this->localeRepository->getActivatedLocaleCodes();
        }

        $context = array_merge($context, [
            'locales' => $this->activatedLocales,
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
                ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX === $format ||
                ProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_MODEL_INDEX === $format ||
                ProductAndProductModel\ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX === $format
            ) && $data instanceof FamilyInterface;
    }
}
