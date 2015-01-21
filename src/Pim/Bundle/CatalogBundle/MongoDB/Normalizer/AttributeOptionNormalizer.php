<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize an attribute option to store it as mongodb_json
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [
            'id'   => $object->getId(),
            'code' => $object->getCode()
        ];

        $values = [];
        foreach ($object->getOptionValues() as $value) {
            $values[$value->getLocale()] = [
                'value'  => $value->getValue(),
                'locale' => $value->getLocale()
            ];
        }
        $data['optionValues'] = $values;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && 'mongodb_json' === $format;
    }
}
