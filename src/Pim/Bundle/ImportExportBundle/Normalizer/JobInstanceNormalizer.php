<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Oro\Bundle\BatchBundle\Entity\JobInstance;

/**
 * A normalizer to transform a job instance entity into a array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface
{
    /**
     * @var array()
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * Transforms an object into a flat array
     *
     * @param object $object
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $results = array(
            'code'          => $object->getCode(),
            'label'         => $object->getLabel(),
            'connector'     => $object->getConnector(),
            'type'          => $object->getType(),
            'configuration' => $this->normalizeConfiguration($object)
        );

        return $results;
    }

    /**
     * Indicates whether this normalizer can normalize the given data
     *
     * @param mixed  $data
     * @param string $format
     *
     * @return boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof JobInstance && in_array($format, $this->supportedFormats);
    }

    /**
     * Get normalized configuration
     *
     * @param JobInstance $job
     *
     * @return mixed
     */
    protected function normalizeConfiguration(JobInstance $job)
    {
        return $job->getRawConfiguration();
    }
}
