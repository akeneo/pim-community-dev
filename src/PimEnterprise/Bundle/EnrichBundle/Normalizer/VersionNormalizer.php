<?php

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\VersioningBundle\Model\Version;
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
    /** @var array */
    protected $supportedFormat = ['array', 'json'];

    /** @var string */
    protected $publProductClass;

    /** @var NormalizerInterface */
    protected $versionNormalizer;

    /** @var ManagerRegistry */
    protected $doctrine;

    public function __construct(NormalizerInterface $versionNormalizer, ManagerRegistry $doctrine, $publProductClass)
    {
        $this->versionNormalizer     = $versionNormalizer;
        $this->doctrine              = $doctrine;
        $this->publProductClass = $publProductClass;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($version, $format = null, array $context = array())
    {
        $normalizedVersion = $this->versionNormalizer->normalize($version, $format, $context);

        $publishedProduct = $this->doctrine->getManagerForClass($this->publProductClass)
                ->getRepository($this->publProductClass)
                ->findOneBy(['version' => $version]);

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
