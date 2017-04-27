<?php

namespace Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        $fileData = null;

        if (null !== $file->getData()) {
            $fileData = [
                'originalFilename' => $file->getData()->getOriginalFilename(),
                'filePath'         => $file->getData()->getKey(),
            ];
        }

        return [
            'locale' => $file->getLocale(),
            'scope'  => $file->getScope(),
            'data'   => $fileData,
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof MediaProductValueInterface;
    }
}
