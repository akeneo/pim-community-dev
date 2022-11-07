<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Versioning;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Version normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
        $this->repository = $repository;
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
    public function supportsNormalization($data, $format = null): bool
    {
        return $this->versionNormalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->versionNormalizer instanceof CacheableSupportsMethodInterface
            && $this->versionNormalizer->hasCacheableSupportsMethod();
    }
}
