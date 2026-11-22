<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

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
    public function normalize($file, $format = null, array $context = []): \ArrayObject|array|string|int|float|bool|null
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
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof FileInfoInterface && in_array($format, $this->supportedFormats);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
