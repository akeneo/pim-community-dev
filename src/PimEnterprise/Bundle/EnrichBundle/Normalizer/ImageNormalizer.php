<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer;
use Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer as BaseImageNormalizer;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;

/**
 * ImageNormalizer.php
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageNormalizer extends BaseImageNormalizer
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        FileNormalizer $fileNormalizer,
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
