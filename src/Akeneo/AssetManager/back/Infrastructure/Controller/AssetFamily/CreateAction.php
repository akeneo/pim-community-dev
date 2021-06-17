<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Creates an asset family
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateAction
{
    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    private NormalizerInterface $normalizer;

    private ValidatorInterface $validator;

    private SecurityFacade $securityFacade;

    public function __construct(
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->normalizer                  = $normalizer;
        $this->validator                   = $validator;
        $this->securityFacade              = $securityFacade;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('akeneo_assetmanager_asset_family_create')) {
            throw new AccessDeniedException();
        }

        $command = $this->getCreateCommand($request);
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            return new JsonResponse($this->normalizer->normalize($violations, 'internal_api'), Response::HTTP_BAD_REQUEST);
        }

        ($this->createAssetFamilyHandler)($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function getCreateCommand(Request $request): CreateAssetFamilyCommand
    {
        $normalizedCommand = json_decode($request->getContent(), true);

        return new CreateAssetFamilyCommand(
            $normalizedCommand['code'],
            $normalizedCommand['labels']
        );
    }
}
