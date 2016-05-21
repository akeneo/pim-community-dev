<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Pim\Component\Catalog\Normalizer\Structured\AttributeGroupNormalizer as BaseNormalizer;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        asort($data['attributes']);
        $data['attributes'] = implode(',', $data['attributes']);

        return $data;
    }
}
