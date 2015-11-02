<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Version normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var PublishedProductRepositoryInterface */
    protected $repository;

    /**
     * @param NormalizerInterface                 $versionNormalizer
     * @param PublishedProductRepositoryInterface $repository
     */
    public function __construct(
        NormalizerInterface $versionNormalizer,
        PublishedProductRepositoryInterface $repository
    ) {
        $this->versionNormalizer = $versionNormalizer;
        $this->repository        = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = [])
    {
        $normalizedVersion = $this->versionNormalizer->normalize($version, $format, $context);
        $publishedProduct = $this->repository->findOneByVersionId($version->getId());

        return array_merge(
            $normalizedVersion,
            [
                'published' => null !== $publishedProduct
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->versionNormalizer->supportsNormalization($data, $format);
    }
}
