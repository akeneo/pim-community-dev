<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;

/**
 * @author    Julien Janvier <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer extends AbstractValueDataNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        return $this->doNormalize($file, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($file, $format = null, array $context = [])
    {
        return [
            $this->getFieldName($file, $context) => $file->getKey(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && in_array($format, $this->supportedFormats);
    }
}
