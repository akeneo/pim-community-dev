<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WebServiceBundle\Handler\Rest;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\WebServiceBundle\Handler\Rest\ProductHandler as BaseProductHandler;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Owerride product handler to apply permissions
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductHandler extends BaseProductHandler
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param SerializerInterface           $serializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(SerializerInterface $serializer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->serializer = $serializer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Serialize a single product
     *
     * @param ProductInterface $product
     * @param string[]         $channels
     * @param string[]         $locales
     * @param string           $url
     *
     * @throws AccessDeniedException
     *
     * @return array
     */
    public function get(ProductInterface $product, $channels, $locales, $url)
    {
        if (false === $this->authorizationChecker->isGranted(Attributes::VIEW, $product)) {
            throw new AccessDeniedException(sprintf('Access denied to the product "%s"', $product->getIdentifier()));
        }

        return parent::get($product, $channels, $locales, $url);
    }
}
