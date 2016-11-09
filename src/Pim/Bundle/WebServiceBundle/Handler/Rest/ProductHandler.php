<?php

namespace Pim\Bundle\WebServiceBundle\Handler\Rest;

use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product handler
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductHandler
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Serialize a single product
     *
     * @param ProductInterface $product
     * @param string[]         $channels
     * @param string[]         $locales
     * @param string           $url
     *
     * @return array
     */
    public function get(ProductInterface $product, $channels, $locales, $url)
    {
        $data = $this->normalizer->normalize(
            $product,
            'standard',
            [
                'locales'      => $locales,
                'channels'     => $channels,
                'filter_types' => ['pim.external_api.product.view']
            ]
        );

        $data['resource'] = $url;

        return $data;
    }
}
