<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Router;

use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * When a product is saved, we send the URI of the product in the headers.
 * This proxy checks if a draft exists for a user and redirect him to product draft route instead of product route.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProxyProductRouter implements UrlGeneratorInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ProductDraftRepositoryInterface */
    private $productDraftRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var string */
    private $productDraftRoute;

    /**
     * @param UrlGeneratorInterface           $router
     * @param TokenStorageInterface           $tokenStorage
     * @param ProductDraftRepositoryInterface $productDraftRepository
     * @param ProductRepositoryInterface      $productRepository
     * @param string                          $productDraftRoute
     */
    public function __construct(
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductRepositoryInterface $productRepository,
        string $productDraftRoute
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->productDraftRepository = $productDraftRepository;
        $this->productRepository = $productRepository;
        $this->productDraftRoute = $productDraftRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (!isset($parameters['code'])) {
            throw new MissingMandatoryParametersException('Parameter "code" is missing in the parameters');
        }

        $product = $this->productRepository->findOneByIdentifier($parameters['code']);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist', $parameters['code']));
        }

        $username = $this->tokenStorage->getToken()->getUser()->getUsername();
        $name = null === $this->productDraftRepository->findUserProductDraft($product, $username)
            ? $name : $this->productDraftRoute;

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        $this->router->getContext();
    }
}
