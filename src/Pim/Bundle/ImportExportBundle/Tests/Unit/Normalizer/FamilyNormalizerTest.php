<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Normalizer;

use Pim\Bundle\ImportExportBundle\Normalizer\FamilyNormalizer;
use Pim\Bundle\ImportExportBundle\Normalizer\TranslationNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

/**
 * Family normalizer test
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->normalizer = new FamilyNormalizer(new TranslationNormalizer());
        $this->format     = 'json';
    }

    /**
     * {@inheritdoc}
     */
    public static function getSupportNormalizationData()
    {
        return [
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'json', true],
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'xml', true],
            ['Pim\Bundle\CatalogBundle\Entity\Family', 'csv', false],
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
                    'code'             => 'mycode',
                    'label'            => ['en_US' => 'My label', 'fr_FR' => 'Mon étiquette'],
                    'attributes'       => ['attribute1', 'attribute2', 'attribute3'],
                    'attributeAsLabel' => 'attribute1',
                    'requirements'     => [
                        'channel1' => ['attribute1', 'attribute2'],
                        'channel2' => ['attribute1', 'attribute3'],
                    ],
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return Family
     */
    protected function createEntity(array $data)
    {
        $family = new Family();
        $family->setCode('mycode');

        foreach ($this->getLabels($data) as $locale => $label) {
            $translation = $family->getTranslation($locale);
            $translation->setLabel($label);
            $family->addTranslation($translation);
        }

        $codes = ['attribute1', 'attribute2', 'attribute3'];
        $attributes = [];
        foreach ($codes as $code) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            $family->addAttribute($attribute);
            $attributes[] = $attribute;
        }

        $family->setAttributeAsLabel(current($attributes));

        $channel1 = new Channel();
        $channel1->setCode('channel1');
        $channel2 = new Channel();
        $channel2->setCode('channel2');

        $requirements = [
            ['attribute' => $attributes[0], 'channel' => $channel1, 'required' => true],
            ['attribute' => $attributes[1], 'channel' => $channel1, 'required' => true],
            ['attribute' => $attributes[2], 'channel' => $channel1, 'required' => false],
            ['attribute' => $attributes[0], 'channel' => $channel2, 'required' => true],
            ['attribute' => $attributes[1], 'channel' => $channel2, 'required' => false],
            ['attribute' => $attributes[2], 'channel' => $channel2, 'required' => true],
        ];
        $attrRequirements = [];
        foreach ($requirements as $requirement) {
            $attrRequirement = new AttributeRequirement();
            $attrRequirement->setAttribute($requirement['attribute']);
            $attrRequirement->setChannel($requirement['channel']);
            $attrRequirement->setRequired($requirement['required']);
            $attrRequirements[] = $attrRequirement;
        }
        $family->setAttributeRequirements($attrRequirements);

        return $family;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    protected function getLabels($data)
    {
        return $data['label'];
    }
}
