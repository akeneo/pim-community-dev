<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * A normalizer to transform a category entity into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var LabelTranslationNormalizer
     */
    protected $translationNormalizer;

    /**
     * Constructor
     *
     * @param LabelTranslationNormalizer $translationNormalizer
     */
    public function __construct(LabelTranslationNormalizer $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'    => $object->getCode(),
            'parent'  => $object->getParent() ? $object->getParent()->getCode() : '',
            'dynamic' => (string) $object->isDynamic(),
        ) + $this->translationNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }
}
