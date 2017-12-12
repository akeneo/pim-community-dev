<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AssetTagController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetTagRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetTagRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $assetTagRepository)
    {
        $this->assetTagRepository = $assetTagRepository;
    }

    /**
     * @param Request $request
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetTag = $this->assetTagRepository->findOneByIdentifier($code);

        if (null === $assetTag) {
            throw new NotFoundHttpException(sprintf('Tag "%s" does not exist.', $code));
        }

        return new JsonResponse(['code' => $assetTag->getCode()]);
    }
}
