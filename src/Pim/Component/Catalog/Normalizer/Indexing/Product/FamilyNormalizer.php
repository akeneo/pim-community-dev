<?php

namespace Pim\Component\Catalog\Normalizer\Indexing\Product;

use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Indexing normalizer for a product family.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /**
     * @param NormalizerInterface $translationNormalizer
     */
    public function __construct(NormalizerInterface $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'code'   => $object->getCode(),
            'labels' => $this->translationNormalizer->normalize($object, $format, $context)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'indexing' === $format && $data instanceof FamilyInterface;
    }
}
