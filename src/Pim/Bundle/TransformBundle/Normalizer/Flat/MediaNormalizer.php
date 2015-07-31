<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;

/**
 * Normalize a media value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 *
 * TODO: should be deleted
 */
class MediaNormalizer extends AbstractProductValueDataNormalizer
{
    /** @var array */
    protected $supportedFormats = array('csv', 'flat');

    /** @var MediaManager */
    protected $manager;

    /**
     * @param MediaManager $manager
     */
    public function __construct(MediaManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Override to be able to return both file path and export path in case of copy
     *
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (isset($context['prepare_copy'])) {
            $identifier = isset($context['identifier']) ? $context['identifier'] : null;

            return [
                'filePath'   => $this->manager->getFilePath($object),
                'exportPath' => $this->manager->getExportPath($object, $identifier)
            ];
        }

        return [
            $this->getFieldName($object, $context) => $this->doNormalize($object, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductMediaInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($object, $format = null, array $context = array())
    {
        $context = $this->resolveContext($context);

        if ($context['versioning']) {
            return $object->getFilename();
        }

        $identifier = isset($context['identifier']) ? $context['identifier'] : null;

        return $this->manager->getExportPath($object, $identifier);
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context = [])
    {
        return array_merge(['versioning' => false], $context);
    }
}
