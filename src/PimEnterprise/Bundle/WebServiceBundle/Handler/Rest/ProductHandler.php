<?php

namespace PimEnterprise\Bundle\WebServiceBundle\Handler\Rest;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Owerride product handler to apply permissions
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductHandler
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SerializerInterface      $serializer
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SerializerInterface $serializer, SecurityContextInterface $securityContext)
    {
        $this->serializer = $serializer;
        $this->securityContext = $securityContext;
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
        if (false === $this->securityContext->isGranted(Attributes::VIEW, $product)) {
           throw new AccessDeniedException(sprintf('Access denied to the product "%s"', $product->getIdentifier()));
        }

        $data = $this->serializer->serialize(
            $product,
            'json',
            [
                'locales'  => $locales,
                'channels' => $channels,
                'resource' => $url
            ]
        );

        return $data;
    }
}
