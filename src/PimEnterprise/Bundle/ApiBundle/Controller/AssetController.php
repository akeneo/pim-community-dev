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
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetRepository
     * @param NormalizerInterface                   $normalizer
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetRepository,
        NormalizerInterface $normalizer
    ) {
        $this->assetRepository = $assetRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException         If the asset does not exist
     * @throws ResourceAccessDeniedException If the user don't even have view permissions on the asset
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function getAction(string $code): Response
    {
        $asset = $this->assetRepository->findOneByIdentifier($code);

        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist.', $code));
        }

        $normalizedAsset = $this->normalizer->normalize($asset, 'external_api');

        return new JsonResponse($normalizedAsset);
    }
}
