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
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param PublishedProductManager       $manager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        PublishedProductManager $manager
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->manager              = $manager;
    }

    /**
     * Publish a product
     *
     * @param Request        $request
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
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
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return JsonResponse
     */
    public function unpublishAction(Request $request)
    {
        $published = $this->findPublishedOr404($request->query->get('originalId'));

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
     * @param integer|string $originalProductId
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
     *
     */
    protected function findPublishedOr404($originalProductId)
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
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
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
