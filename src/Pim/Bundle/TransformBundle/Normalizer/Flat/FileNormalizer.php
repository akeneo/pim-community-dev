<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInterface;

/**
 * @author    Julien Janvier <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: spec it
 */
class FileNormalizer extends AbstractProductValueDataNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function doNormalize($file, $format = null, array $context = array())
    {
        return [
            'filePath' => $file->getKey(),
            'originalFilename' => $file->getOriginalFilename(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInterface && in_array($format, $this->supportedFormats);
    }
}
