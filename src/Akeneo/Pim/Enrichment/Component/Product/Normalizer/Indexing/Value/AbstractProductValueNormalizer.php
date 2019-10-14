<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Abstract product value normalizer providing a product value path builder
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProductValueNormalizer implements NormalizerInterface
{
    /** @var GetAttributes */
    protected $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productValue, $format = null, array $context = [])
    {
        $locale = (null === $productValue->getLocaleCode()) ? '<all_locales>' : $productValue->getLocaleCode();
        $channel = (null === $productValue->getScopeCode()) ? '<all_channels>' : $productValue->getScopeCode();

        $attribute = $this->getAttributes->forCode($productValue->getAttributeCode());

        if ($attribute !== null) {
            $key = $attribute->code() . '-' . $attribute->backendType();
            $structure = [];
            $structure[$key][$channel][$locale] = $this->getNormalizedData($productValue);

            return $structure;
        } else {
            return null;
        }
    }

    /**
     * Normalizes the product value data to the indexing format
     *
     * @param ValueInterface $value
     *
     * @return mixed
     **/
    abstract protected function getNormalizedData(ValueInterface $value);
}
