<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\ProductPriceInterface;

/**
 * Normalize a product price
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer extends AbstractValueDataNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductPriceInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * {@inheritdoc}
     */
    protected function doNormalize($object, $format = null, array $context = [])
    {
        $data = $object->getData();
        if (null !== $data && '' !== $data) {
            $data = sprintf('%.2F', $data);
        }

        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldName($object, array $context = [])
    {
        return sprintf('%s-%s', parent::getFieldName($object, $context), $object->getCurrency());
    }
}
