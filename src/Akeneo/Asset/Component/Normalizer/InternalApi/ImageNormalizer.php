<?php

namespace Akeneo\Asset\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer as BaseImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        FileNormalizer $fileNormalizer,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        parent::__construct($fileNormalizer);

        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
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
        if (!$value instanceof ReferenceDataCollectionValue) {
            return false;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        return (null !== $attribute &&
            $attribute->getReferenceDataName() === 'assets');
    }
}
