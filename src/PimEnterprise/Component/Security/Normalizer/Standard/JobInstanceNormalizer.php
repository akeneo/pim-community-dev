<?php

namespace PimEnterprise\Component\Security\Normalizer\Standard;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a job instance entity into a array with permission
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var JobProfileAccessManager */
    protected $accessManager;

    /**
     * @param NormalizerInterface     $normalizer
     * @param JobProfileAccessManager $accessManager
     */
    public function __construct(NormalizerInterface $normalizer, JobProfileAccessManager $accessManager)
    {
        $this->normalizer    = $normalizer;
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $normalizedJobInstance = $this->normalizer->normalize($object, $format, $context);

        $normalizedJobInstance['permissions'] = [
            'execute' => array_map(function ($permission) {
                return $permission->getName();
            }, $this->accessManager->getExecuteUserGroups($object)),
            'edit'   => array_map(function ($permission) {
                return $permission->getName();
            }, $this->accessManager->getEditUserGroups($object))
        ];

        return $normalizedJobInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
