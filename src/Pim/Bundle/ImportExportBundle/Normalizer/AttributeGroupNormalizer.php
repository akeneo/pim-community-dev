<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /**
     * @var string[]
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var array
     */
    protected $results;

    /**
     * {@inheritdoc}
     */
    public function normalize($group, $format = null, array $context = array())
    {
        $this->results = array(
            'code'       => $group->getCode(),
            'name'       => $this->normalizeName($group),
            'sortOrder'  => $group->getSortOrder(),
            'attributes' => $this->normalizeAttributes($group)
        );

        return $this->results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroup && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the name
     *
     * @param AttributeGroup $group
     *
     * @return array
     */
    protected function normalizeName(AttributeGroup $group)
    {
        $names = array();
        foreach ($group->getTranslations() as $translation) {
            $names[$translation->getLocale()] = $translation->getName();
        }

        return $names;
    }

    /**
     * Normalize the attributes
     *
     * @param AttributeGroup $group
     *
     * @return array
     */
    protected function normalizeAttributes(AttributeGroup $group)
    {
        $attributes = array();
        foreach ($group->getAttributes() as $attribute) {
            $attributes[]= $attribute->getCode();
        }

        return $attributes;
    }
}
