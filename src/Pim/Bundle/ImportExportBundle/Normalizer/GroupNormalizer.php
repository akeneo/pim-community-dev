<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * A normalizer to transform a group entity into a array
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
    protected $supportedFormats = array('json', 'xml');

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $results = array(
            'code' => $object->getCode(),
            'type' => $object->getType()->getCode(),
            'attributes' => $this->normalizeAttributes($object)
        ) + $this->normalizeLabel($object);

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
     * Returns an array containing the label values
     *
     * @param Group $group
     *
     * @return array
     */
    protected function normalizeLabel(Group $group)
    {
        $labels = array();
        foreach ($group->getTranslations() as $group) {
            $labels[$group->getLocale()]= $group->getLabel();
        }

        return array('label' => $labels);
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
        $attributes = array();
        foreach ($group->getAttributes() as $attribute) {
            $attributes[]= $attribute->getCode();
        }
        sort($attributes);

        return $attributes;
    }
}
