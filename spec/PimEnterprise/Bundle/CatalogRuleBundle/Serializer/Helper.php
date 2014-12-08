<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

class Helper
{
    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @return array
     */
    public static function buildRules() {
        return [
            'discharge_fr_description' => [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                    ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
                'actions' => [
                    ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['type' => 'copy_value', 'from_field' => 'description', 'to_field' => 'description', 'from_locale' => 'fr_FR', 'to_locale' => 'fr_CH'],
                ],
            ],
            'set_size' => [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ],
                'actions' => [
                    ['type' => 'set_value', 'field' => 'size', 'value' => 'XL'],
                ],
            ],
        ];
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @param bool $encode
     *
     * @return string
     */
    public static function buildRuleContent($encode = false)
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'from_field' => 'description', 'to_field' => 'description', 'from_locale' => 'fr_FR', 'to_locale' => 'fr_CH']
            ]
        ];

        if (true === $encode) {
            $content = json_encode($content);
        }

        return $content;
    }
}
