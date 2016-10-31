<?php

namespace Pim\Bundle\ReferenceDataBundle\Controller;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Configuration controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationRestController
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ConfigurationRegistryInterface $registry
     * @param NormalizerInterface            $normalizer
     */
    public function __construct(ConfigurationRegistryInterface $registry, NormalizerInterface $normalizer)
    {
        $this->registry = $registry;
        $this->normalizer = $normalizer;
    }

    /**
     * Get the reference data configuration
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $referenceConfig = $this->registry->all();

        return new JsonResponse($this->normalizer->normalize($referenceConfig, 'internal_api'));
    }
}
