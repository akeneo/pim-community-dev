<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $this->manager              = $manager;
        $this->userContext          = $userContext;
        $this->productNormalizer    = $productNormalizer;
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
        $channels         = array_keys($this->userContext->getChannelChoicesWithUserChannel());
        $locales          = $this->userContext->getUserLocaleCodes();

        return new JsonResponse(
            $this->productNormalizer->normalize(
                $publishedProduct,
                'internal_api',
                [
                    'locales'                    => $locales,
                    'channels'                   => $channels,
                    'filter_type'                => 'pim.internal_api.product_value.view',
                    'locale'                     => $this->userContext->getUiLocale()->getCode(),
                    'disable_grouping_separator' => true,
                ]
            )
        );
    }

    /**
     * Publish a product
     *
     * @param Request        $request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws AccessDeniedException
     * @return JsonResponse
     */
    public function publishAction(Request $request)
    {
        $product = $this->findOr404($request->query->get('originalId'));

        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->publish($product);

        return new JsonResponse();
    }

    /**
     * Unpublish a product
     *
     * @param Request        $request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws AccessDeniedException
     * @return JsonResponse
     */
    public function unpublishAction(Request $request)
    {
        $published = $this->findPublishedByOriginalIdOr404($request->query->get('originalId'));

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
     * @param integer|string $publishedProductId
     *
     * @throws NotFoundHttpException
     * @return PublishedProductInterface
     *
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
     * @param integer|string $originalProductId
     *
     * @throws NotFoundHttpException
     * @return PublishedProductInterface
     *
     */
    protected function findPublishedByOriginalIdOr404($originalProductId)
    {
        $published = $this->manager->findPublishedProductByOriginalId($originalProductId);

        if (!$published) {
            throw new NotFoundHttpException(sprintf(
                'Published product with original id %s could not be found.',
                (string) $originalProductId
            ));
        }

        return $published;
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer|string $originalProductId
     *
     * @throws NotFoundHttpException
     * @return PublishedProductInterface
     *
     */
    protected function findOr404($originalProductId)
    {
        $product = $this->manager->findOriginalProduct($originalProductId);

        if (!$product) {
            throw new NotFoundHttpException(sprintf(
                'Product with original id %s could not be found.',
                (string) $originalProductId
            ));
        }

        return $product;
    }
}
