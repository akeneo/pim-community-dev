<?php

namespace PimEnterprise\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use PimEnterprise\Component\ProductAsset\Model\Asset;
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
    /**
     * {@inheritdoc}
     */
    public function normalize($value, $format = null, array $context = [])
    {
        $data = $value->getData();
        if (!isset($data[0])) {
            return null;
        }

        $fileInfo = $value->getData()[0]->getReference()->getFileInfo();
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
