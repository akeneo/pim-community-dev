<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Published product controller
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class PublishedProductRestController
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var PublishedProductManager */
    protected $manager;

    /**
     * @param SecurityContextInterface $securityContext
     * @param PublishedProductManager  $manager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        PublishedProductManager $manager
    ) {
        $this->securityContext = $securityContext;
        $this->manager         = $manager;
    }

    /**
     * Publish a product
     *
     * @param Request        $request
     * @param integer|string $id
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function publishAction(Request $request)
    {
        $product = $this->findOr404($request->query->get('originalId'));

        $isOwner = $this->securityContext->isGranted(Attributes::OWN, $product);
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
     * @param integer|string $id
     *
     * @AclAncestor("pimee_workflow_published_product_index")
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function unpublishAction(Request $request)
    {
        $published = $this->findPublishedOr404($request->query->get('originalId'));

        $isOwner = $this->securityContext->isGranted(Attributes::OWN, $published->getOriginalProduct());
        if (!$isOwner) {
            throw new AccessDeniedException();
        }

        $this->manager->unpublish($published);

        return new JsonResponse();
    }

    /**
     * Find a published product by its original product id or return a 404 response
     *
     * @param integer|string $id
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @param integer|string $id
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
