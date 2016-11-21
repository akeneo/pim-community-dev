<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface
{
    /**  @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $results = [
            'code'          => $object->getCode(),
            'label'         => $object->getLabel(),
            'connector'     => $object->getConnector(),
            'type'          => $object->getType(),
            'configuration' => json_encode($object->getRawParameters()),
        ];

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobInstance && in_array($format, $this->supportedFormats);
    }
}
