<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;

/**
 * @author    Julien Janvier <janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer extends AbstractProductValueDataNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var FileExporterPathGeneratorInterface */
    protected $pathGenerator;

    /**
     * @param FileExporterPathGeneratorInterface $pathGenerator
     */
    public function __construct(FileExporterPathGeneratorInterface $pathGenerator)
    {
        $this->pathGenerator = $pathGenerator;
    }

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
        /**
         * "prepare_copy" is used for medias export
         *      [
         *          'storageAlias' => 'my_file_storage',
         *          'filePath' => '9/4/0/c/940cce20eaaef7fb622f1f9f4f8a0e3e11271d86_SNKRS_1R.png'
         *          'exportPath' => 'files/SNKRS-1B/side_view/akene-mobile.jpg'
         *      ]
         */
        if (isset($context['prepare_copy']) && true === $context['prepare_copy'] && isset($context['value'])) {
            $identifier = isset($context['identifier']) ? $context['identifier'] : null;

            return [
                'storageAlias' => $file->getStorage(),
                'filePath'     => $file->getKey(),
                'exportPath'   => $this->pathGenerator->generate($context['value'], ['identifier' => $identifier])
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

        if (isset($context['value'])) {
            $exportPath = $this->pathGenerator->generate($context['value'], ['identifier' => $identifier]);

            return [
                $this->getFieldName($file, $context) => $exportPath,
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && in_array($format, $this->supportedFormats);
    }
}
