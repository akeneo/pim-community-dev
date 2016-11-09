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

use Pim\Bundle\WebServiceBundle\Handler\Rest\ProductHandler as BaseProductHandler;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Owerride product handler to apply permissions
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductHandler extends BaseProductHandler
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param NormalizerInterface           $normalizer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(NormalizerInterface $normalizer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->normalizer = $normalizer;
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
