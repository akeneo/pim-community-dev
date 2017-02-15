<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use Pim\Bundle\CatalogBundle\AttributeType\TextCollectionType;

/**
 * Converts a text collection from Akeneo PIM flat format to Akeneo PIM standard format
 *
 * Flat format:
 * [
 *      'my_collection-en_US' => "foo;bar;baz",
 * ]
 *
 * Standard format:
 * [
 *      'my-collection' => [{
 *          "locale": "en_US",
 *          "scope": null,
 *          "data": [
 *              "foo",
 *              "bar",
 *              "baz",
 *          ]
 *      }]
 * ]
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextCollectionConverter implements ValueConverterInterface
{
    /** @var string[] */
    protected $supportedFieldTypes;

    /**
     * @param string[] $supportedFieldTypes
     */
    public function __construct(array $supportedFieldTypes)
    {
        $this->supportedFieldTypes = $supportedFieldTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($attributeType)
    {
        return in_array($attributeType, $this->supportedFieldTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $attributeFieldInfo, $value)
    {
        if ('' === trim($value)) {
            $data = [];
        } else {
            $data = explode(TextCollectionType::FLAT_SEPARATOR, $value);
        }

        return [
            $attributeFieldInfo['attribute']->getCode() => [[
                'locale' => $attributeFieldInfo['locale_code'],
                'scope'  => $attributeFieldInfo['scope_code'],
                'data'   => $data,
            ]],
        ];
    }
}
