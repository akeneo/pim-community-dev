<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

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
    public function normalize($file, $format = null, array $context = array())
    {
        return $this->doNormalize($file, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($file, $format = null, array $context = array())
    {
        /**
         * "prepare_copy" is used for medias export
         *      [
         *          'storageAlias' => 'my_file_storage',
         *          'filePath' => '9/4/0/c/940cce20eaaef7fb622f1f9f4f8a0e3e11271d86_SNKRS_1R.png'
         *          'exportPath' => 'files/SNKRS-1B/side_view/akene-mobile.jpg'
         *      ]
         */
        if (isset($context['prepare_copy']) && true === $context['prepare_copy']) {
            $identifier = isset($context['identifier']) ? $context['identifier'] : null;

            return [
                'storageAlias' => $file->getStorage(),
                'filePath'     => $file->getKey(),
                'exportPath'   => $this->getExportPath($context['value'], $identifier)
            ];
        }

        /**
         * "versioning" is used for versioning
         *      [
         *          'media' => '9/4/0/c/940cce20eaaef7fb622f1f9f4f8a0e3e11271d86_SNKRS_1R.png'
         *      ]
         */
        if (isset($context['versioning']) && true === $context['versioning']) {
            return [
                $this->getFieldName($file, $context) => $file->getKey(),
            ];
        }

        /**
         * other case is used by products exports (to retrieve the path of the media for the export)
         *      [
         *          'media' => 'files/SNKRS-1B/side_view/akene-mobile.jpg'
         *      ]
         */
        $identifier = isset($context['identifier']) ? $context['identifier'] : null;

        return [
            $this->getFieldName($file, $context) => $this->getExportPath($context['value'], $identifier),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInterface && in_array($format, $this->supportedFormats);
    }

    //TODO: should be a service
    protected function getExportPath(ProductValueInterface $value, $identifier = null)
    {
        if (null === $file = $value->getMedia()) {
            return '';
        }

        $attribute = $value->getAttribute();

        $identifier = null !== $identifier ? $identifier : $value->getEntity()->getIdentifier();
        $target = sprintf('files/%s/%s', $identifier, $attribute->getCode());

        if ($attribute->isLocalizable()) {
            $target .= '/' . $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $target .= '/' . $value->getScope();
        }

        return $target . '/' . $file->getOriginalFilename();
    }
}
