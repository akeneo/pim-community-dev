<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Abstract product value normalizer providing a product value path builder
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProductValueNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    /**
     * @param LruArrayAttributeRepository $attributeRepository
     */
    public function __construct(LruArrayAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productValue, $format = null, array $context = [])
    {
        $locale = (null === $productValue->getLocaleCode()) ? '<all_locales>' : $productValue->getLocaleCode();
        $channel = (null === $productValue->getScopeCode()) ? '<all_channels>' : $productValue->getScopeCode();

        $attribute = $this->attributeRepository->findOneByIdentifier($productValue->getAttributeCode());

        if ($attribute !== null) {
            $key = $attribute->getCode() . '-' . $attribute->getBackendType();
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
