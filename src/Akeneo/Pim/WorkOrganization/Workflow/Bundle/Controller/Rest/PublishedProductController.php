<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Controller\Rest;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\ProductHasNoIdentifierException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product controller
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var UserContext */
    protected $userContext;

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param PublishedProductManager       $manager
     * @param UserContext                   $userContext
     * @param NormalizerInterface           $productNormalizer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        PublishedProductManager $manager,
        UserContext $userContext,
        NormalizerInterface $productNormalizer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->manager = $manager;
        $this->userContext = $userContext;
        $this->productNormalizer = $productNormalizer;
    }

    /**
     * @param string $id Published product id
     *
     * @throws NotFoundHttpException If published product is not found or the user cannot see it
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $publishedProduct = $this->findPublishedOr404($id);
        $channels = array_keys($this->userContext->getChannelChoicesWithUserChannel());
        $locales = $this->userContext->getUserLocaleCodes();

        return new JsonResponse(
            $this->productNormalizer->normalize(
                $publishedProduct,
                'internal_api',
                [
                    'locales'                    => $locales,
                    'channels'                   => $channels,
                    'filter_types'               => ['pim.internal_api.product_value.view'],
                    'locale'                     => $this->userContext->getUiLocale()->getCode(),
                    'disable_grouping_separator' => true,
                ]
            )
        );
    }

    /**
     * Publish a product
     *
     * @param Request $request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function publishAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findOr404($request->query->get('originalUuid'));

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        try {
            $this->manager->publish($product);
        } catch (ProductHasNoIdentifierException $e) {
            return new JsonResponse(['message' => 'pimee_enrich.entity.published_product.flash.publish.fail_no_identifier'], 422);
        }

        return new JsonResponse();
    }

    /**
     * Unpublish a product
     *
     * @param Request $request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function unpublishAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $published = $this->findPublishedByOriginalIdOr404($request->query->get('originalUuid'));

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $published->getOriginalProduct());
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->unpublish($published);

        return new JsonResponse();
    }

    /**
     * Find a published product by its original product id or return a 404 response
     *
     * @param int|string $publishedProductId
     *
     * @throws NotFoundHttpException
     *
     * @return PublishedProductInterface
     */
    protected function findPublishedOr404($publishedProductId)
    {
        $published = $this->manager->findPublishedProductById($publishedProductId);

        if (!$published) {
            throw new NotFoundHttpException(sprintf(
                'Published product with id %s could not be found.',
                (string) $publishedProductId
            ));
        }

        return $published;
    }

    /**
     * Find a published product by its original product id or return a 404 response
     *
     * @param string $originalProductUuid
     *
     * @return PublishedProductInterface
     * @throws NotFoundHttpException
     */
    protected function findPublishedByOriginalIdOr404(string $originalProductUuid)
    {
        $published = $this->manager->findPublishedProductByOriginalUuid($originalProductUuid);

        if (!$published) {
            throw new NotFoundHttpException(sprintf(
                'Published product with original uuid %s could not be found.',
                (string) $originalProductUuid
            ));
        }

        return $published;
    }

    /**
     * Find a product by its uuid or return a 404 response
     *
     * @param string $originalProductUuid
     *
     * @return PublishedProductInterface
     * @throws NotFoundHttpException
     */
    protected function findOr404(string $originalProductUuid)
    {
        $product = $this->manager->findOriginalProduct($originalProductUuid);

        if (!$product) {
            throw new NotFoundHttpException(sprintf(
                'Product with original uuid %s could not be found.',
                (string) $originalProductUuid
            ));
        }

        return $product;
    }
}
