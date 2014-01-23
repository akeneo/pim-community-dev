<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\GroupNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\TranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new GroupNormalizer(new TranslationNormalizer());
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Entity\Group', 'json', true],
            ['Pim\Bundle\CatalogBundle\Entity\Group', 'xml', true],
            ['Pim\Bundle\CatalogBundle\Entity\Group', 'csv', false],
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
                    'code'        => 'my_variant_group',
                    'type'        => 'VARIANT',
                    'label-en_US' => 'My variant group',
                    'label-fr_FR' => 'Mon groupe variant',
                    'attributes'  => ['color', 'size']
                ],
                [
                    'code'       => 'my_group',
                    'type'       => 'RELATED',
                    'label'      => ['en' => 'My group', 'fr' => 'Mon group'],
                    'attributes' => []
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return Group
     */
    protected function createEntity(array $data)
    {
        $group = new Group();
        $group->setCode($data['code']);

        $type = new GroupType();
        $type->setCode($data['type']);
        $type->setVariant(($data['type'] === 'VARIANT'));
        $group->setType($type);

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $group->getTranslation($locale);
            $translation->setLabel($label);
            $group->addTranslation($translation);
        }

        foreach ($this->getAttributes($data) as $attribute) {
            $group->addAttribute($attribute);
        }

        return $group;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getAttributes($data)
    {
        $attributes = [];
        $codes = $data['attributes'];
        foreach ($codes as $code) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            $attributes[] = $attribute;
        }

        return $attributes;
    }
}
