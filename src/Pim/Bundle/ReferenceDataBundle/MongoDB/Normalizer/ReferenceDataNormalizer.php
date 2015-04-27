<?php

namespace Pim\Bundle\ReferenceDataBundle\MongoDB\Normalizer;

use Pim\Component\ReferenceData\LabelRenderer;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a reference data very simply to store it as mongodb_json
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface
{
    /** @var LabelRenderer */
    protected $renderer;

    /**
     * Constructor
     *
     * @param LabelRenderer $renderer
     */
    public function __construct(LabelRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [
            'id'   => $object->getId(),
            'code' => $object->getCode()
        ];

        if (null !== $label = $this->renderer->render($object, false)) {
            $data[$this->renderer->getLabelProperty($object)] = $label;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ReferenceDataInterface && 'mongodb_json' === $format;
    }
}
