<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var TranslationNormalizer $transNormalizer
     */
    protected $transNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $transNormalizer
     */
    public function __construct(TranslationNormalizer $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'    => $object->getCode(),
            'parent'  => $object->getParent() ? $object->getParent()->getCode() : '',
        ) + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }
}
