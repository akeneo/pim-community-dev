<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\AttributeGroupNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\TranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Attribute group normalizer test
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new AttributeGroupNormalizer(new TranslationNormalizer());
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'json', true],
            ['Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'xml', true],
            ['Pim\Bundle\CatalogBundle\Entity\AttributeGroup', 'csv', false],
            ['stdClass', 'json', false],
            ['stdClass', 'xml', false],
            ['stdClass', 'csv', false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getNormalizeData()
    {
        return [
            [
                [
                    'code'        => 'mycode',
                    'label'       => ['en_US' => 'My name', 'fr_FR' => 'Mon nom'],
                    'sortOrder'   => 5,
                    'attributes'  => ['attribute1', 'attribute2', 'attribute3']
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return AttributeGroup
     */
    protected function createEntity(array $data)
    {
        $group = new AttributeGroup();
        $group->setCode($data['code']);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $group->getTranslation($locale);
            $translation->setLabel($label);
            $group->addTranslation($translation);
        }

        $group->setSortOrder($data['sortOrder']);

        foreach ($this->getAttributes($data) as $attribute) {
            $group->addAttribute($attribute);
        }

        return $group;
    }

    /**
     * Get labels
     * @param array $data
     *
     * @return array
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }

    /**
     * Get attributes
     * @param array $data
     *
     * @return Attribute[]
     */
    protected function getAttributes($data)
    {
        $attributes = [];
        foreach ($data['attributes'] as $code) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
