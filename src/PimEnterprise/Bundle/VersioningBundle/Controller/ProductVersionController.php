<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\VersioningBundle\Reverter\ProductReverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product version controller
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductVersionController
{
    /** @var ProductReverter */
    protected $reverter;

    /** @var string */
    protected $versionClass;

    /**
     * @param ManagerRegistry          $doctrine
     * @param string                   $versionClass
     * @param ProductReverter          $reverter
     */
    public function __construct(
        ManagerRegistry $doctrine,
        $versionClass,
        ProductReverter $reverter
    ) {
        $this->doctrine     = $doctrine;
        $this->versionClass = $versionClass;
        $this->reverter     = $reverter;
    }

    /**
     * Revert the entity to the current version
     *
     * @param string|int $id
     *
     * @return RedirectResponse
     *
     * @AclAncestor("pimee_versioning_product_version_revert")
     */
    public function revertAction($id)
    {
        try {
            $version = $this->findOr404($this->versionClass, $id);
            $this->reverter->revert($version);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse([], 200);
    }

    /**
     * Find an entity or throw a 404
     *
     * @param string  $className Example: 'PimCatalogBundle:Category'
     * @param integer $id        The id of the entity
     *
     * @throws NotFoundHttpException
     * @return object
     */
    protected function findOr404($className, $id)
    {
        $result = $this->doctrine->getRepository($className)->find($id);

        if (!$result) {
            throw NotFoundHttpException(sprintf('%s entity not found', $className));
        }

        return $result;
    }
}
