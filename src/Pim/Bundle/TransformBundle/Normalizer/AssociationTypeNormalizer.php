<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

/**
 * Association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer implements NormalizerInterface
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
            'code'  => $object->getCode()
        ) + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AssociationType && in_array($format, $this->supportedFormats);
    }
}
