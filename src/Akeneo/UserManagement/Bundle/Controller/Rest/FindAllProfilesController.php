<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\UserManagement\Component\Storage\ProfileRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FindAllProfilesController
{
    private ProfileRepositoryInterface $profileRepository;

    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return new JsonResponse($this->profileRepository->findAll());
    }
}
