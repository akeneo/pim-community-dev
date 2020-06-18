<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\ProductRevert\Controller;

use Akeneo\Pim\WorkOrganization\ProductRevert\Exception\ConstraintViolationListException;
use Akeneo\Pim\WorkOrganization\ProductRevert\Reverter\ProductReverter;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        ManagerRegistry $doctrine,
        string $versionClass,
        ProductReverter $reverter,
        NormalizerInterface $normalizer
    ) {
        $this->doctrine = $doctrine;
        $this->versionClass = $versionClass;
        $this->reverter = $reverter;
        $this->normalizer = $normalizer;
    }

    /**
     * Revert the entity to the current version
     *
     * @param string|int $id
     *
     * @return Response
     *
     * @AclAncestor("pimee_revert_product_version_revert")
     */
    public function revertAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $version = $this->findOr404($this->versionClass, $id);
            $this->reverter->revert($version);
        } catch (ConstraintViolationListException $e) {
            $normalizedResponse = $this->normalizer->normalize(
                $e->getConstraintViolationList(),
                'internal_api',
                ['translate' => false]
            );

            return new JsonResponse($normalizedResponse, 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse([], 200);
    }

    /**
     * Find an entity or throw a 404
     *
     * @param string $className Example: 'PimCatalogBundle:Category'
     * @param int    $id        The id of the entity
     *
     * @throws NotFoundHttpException
     *
     * @return Version
     */
    protected function findOr404(string $className, int $id): Version
    {
        $result = $this->doctrine->getRepository($className)->find($id);
        if (!$result instanceof Version) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $className));
        }

        return $result;
    }
}
