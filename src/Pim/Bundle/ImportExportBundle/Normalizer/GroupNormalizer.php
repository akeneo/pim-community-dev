<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * A normalizer to transform a group entity into an array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = ['json', 'xml'];

    /**
     * @var TranslationNormalizer
     */
    protected $translationNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(TranslationNormalizer $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $results = [
            'code' => $object->getCode(),
            'type' => $object->getType()->getCode(),
            'attributes' => $this->normalizeAttributes($object)
        ] + $this->translationNormalizer->normalize($object, $format, $context);

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Group && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param Group $group
     *
     * @return array
     */
    protected function normalizeAttributes(Group $group)
    {
        $attributes = [];
        foreach ($group->getAttributes() as $attribute) {
            $attributes[] = $attribute->getCode();
        }
        sort($attributes);

        return $attributes;
    }
}
