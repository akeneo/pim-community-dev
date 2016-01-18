<?php

namespace Pim\Bundle\WebServiceBundle\Handler\Rest;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product handler
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductHandler
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
        $data = $this->serializer->serialize(
            $product,
            'json',
            [
                'locales'     => $locales,
                'channels'    => $channels,
                'resource'    => $url,
                'filter_type' => 'pim.external_api.product.view'
            ]
        );

        return $data;
    }
}
