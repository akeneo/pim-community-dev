<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\FileNormalizer;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValue;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * AssetCollection Normalizer
 * Used to normalize the first asset of an AssetCollection
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionNormalizer implements NormalizerInterface
{
    /** @var FileNormalizer */
    protected $fileNormalizer;

    /** @var string[] */
    protected $supportedFormats = ['internal_api'];

    /**
     * @param FileNormalizer $fileNormalizer
     */
    public function __construct(FileNormalizer $fileNormalizer)
    {
        $this->fileNormalizer = $fileNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $fileInfo = $object->getData()[0]->getReference()->getFileInfo();

        return $this->fileNormalizer->normalize($fileInfo, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        if (!in_array($format, $this->supportedFormats)) {
            return false;
        }

        if (!($data instanceof ReferenceDataCollectionValue)) {
            return false;
        }

        if (null !== $data->getData() && isset($data->getData()[0])) {
            $firstData = $data->getData()[0];
            if (!($firstData instanceof Asset)) {
                return false;
            }
        }

        return true;
    }
}
