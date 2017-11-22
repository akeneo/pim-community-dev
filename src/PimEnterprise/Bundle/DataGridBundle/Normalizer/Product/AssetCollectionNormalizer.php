<?php

namespace PimEnterprise\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes the a value of an AssetCollection.
 * It returns file info about the first asset in the collection to display in datagrid.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionNormalizer implements NormalizerInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        $data = $value->getData();
        if (empty($data)) {
            return null;
        }

        $locale = $this->localeRepository->findOneByIdentifier($context['data_locale']);
        $fileInfo = $value->getData()[0]->getReference($locale)->getFileInfo();
        if (null === $fileInfo) {
            return null;
        }

        $fileData = [
            'originalFilename' => $fileInfo->getOriginalFilename(),
            'filePath'         => $fileInfo->getKey(),
        ];

        return [
            'data' => $fileData,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        if ('datagrid' !== $format) {
            return false;
        }

        if (!($data instanceof ReferenceDataCollectionValue)) {
            return false;
        }

        return $data->getAttribute()->getReferenceDataName() === 'assets';
    }
}
