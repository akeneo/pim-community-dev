<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Association type normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected array $supportedFormats = ['internal_api'];

    public function __construct(
        protected NormalizerInterface $normalizer,
        protected VersionManager $versionManager,
        protected NormalizerInterface $versionNormalizer
    ) {
    }

    /**
     * @param AssociationTypeInterface $object
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = $this->normalizer->normalize($object, 'standard', $context);

        $firstVersion = $this->versionManager->getOldestLogEntry($object);
        $lastVersion = $this->versionManager->getNewestLogEntry($object);

        $firstVersion = null !== $firstVersion ?
            $this->versionNormalizer->normalize($firstVersion, 'internal_api') :
            null;

        $lastVersion = null !== $lastVersion ?
            $this->versionNormalizer->normalize($lastVersion, 'internal_api') :
            null;

        $result['meta'] = [
            'id'                => $object->getId(),
            'form'              => 'pim-association-type-edit-form',
            'model_type'        => 'association_type',
            'created'           => $firstVersion,
            'updated'           => $lastVersion,
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AssociationTypeInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
