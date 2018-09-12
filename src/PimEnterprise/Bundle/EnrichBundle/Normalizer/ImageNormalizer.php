<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer as BaseImageNormalizer;
use Akeneo\Tool\Component\FileStorage\Normalizer\FileNormalizer;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;

/**
 * Image Normalizer for Enterprise Edition.
 *
 * This Image Normalizer is able to normalize images coming from Asset attributes, to displau the first asset of a
 * collection.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 */
class ImageNormalizer extends BaseImageNormalizer
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param \Akeneo\Tool\Component\FileStorage\Normalizer\FileNormalizer $fileNormalizer
     * @param LocaleRepositoryInterface                                    $localeRepository
     */
    public function __construct(
        \Akeneo\Tool\Component\FileStorage\Normalizer\FileNormalizer $fileNormalizer,
        LocaleRepositoryInterface $localeRepository
    ) {
        parent::__construct($fileNormalizer);

        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(?ValueInterface $value, ?string $localeCode = null): ?array
    {
        if ($this->isAssetCollection($value)) {
            $data = $value->getData();
            if (empty($data)) {
                return null;
            }

            $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            $fileInfo = $data[0]->getReference($locale)->getFileInfo();
            if (null === $fileInfo) {
                return null;
            }

            return $this->fileNormalizer->normalize($fileInfo);
        }

        return parent::normalize($value, $localeCode);
    }

    private function isAssetCollection($value)
    {
        return (
            ($value instanceof ReferenceDataCollectionValue) &&
            $value->getAttribute()->getReferenceDataName() === 'assets'
        );
    }
}
