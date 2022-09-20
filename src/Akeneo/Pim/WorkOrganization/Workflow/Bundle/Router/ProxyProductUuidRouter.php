<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * When patching a product via API, the URI of the product is provided in the response header.
 * This proxy checks if a draft exists for the user and replaces the product route by the product draft route.
 */
final class ProxyProductUuidRouter implements UrlGeneratorInterface
{
    private const PRODUCT_ROUTE = 'pim_api_product_uuid_get';
    private const DRAFT_ROUTE = 'pimee_api_product_draft_get_with_uuid';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ProductRepositoryInterface $productRepository,
        private EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (self::PRODUCT_ROUTE === $name) {
            if (!isset($parameters['uuid'])) {
                throw new MissingMandatoryParametersException('Parameter "uuid" is missing in the parameters');
            }
            $product = $this->productRepository->find($parameters['uuid']);
            if (null === $product) {
                throw new NotFoundHttpException(sprintf('Product "%s" does not exist', $parameters['uuid']));
            }

            $username = $this->tokenStorage->getToken()?->getUser()?->getUserIdentifier();
            if (null !== $username && $this->draftExistsFor($product, $username)) {
                return $this->generate(self::DRAFT_ROUTE, $parameters, $referenceType);
            }
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context): void
    {
        $this->urlGenerator->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): RequestContext
    {
        return $this->urlGenerator->getContext();
    }

    private function draftExistsFor(ProductInterface $originalProduct, string $username): bool
    {
        return null !== $this->productDraftRepository->findUserEntityWithValuesDraft($originalProduct, $username);
    }
}
