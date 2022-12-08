<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Controller;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageHydratorInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Platform\JobAutomation\Application\CheckStorageConnection\CheckStorageConnectionHandler;
use Akeneo\Platform\JobAutomation\Application\CheckStorageConnection\CheckStorageConnectionQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetStorageConnectionCheckAction
{
    public function __construct(
        private readonly CheckStorageConnectionHandler $checkStorageConnectionHandler,
        private readonly ValidatorInterface $validator,
        private readonly NormalizerInterface $normalizer,
        private readonly StorageHydratorInterface $storageHydrator,
        private readonly CredentialsEncrypterRegistry $credentialsEncrypterRegistry,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        $encryptedData = $this->credentialsEncrypterRegistry->encryptCredentials($data);

        $violations = $this->validator->validate($encryptedData, new Storage(['xlsx', 'csv', 'zip']));
        if (0 < $violations->count()) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $storage = $this->storageHydrator->hydrate($encryptedData);
        $connectionCheck = $this->checkStorageConnectionHandler->handle(new CheckStorageConnectionQuery($storage));

        return new JsonResponse([], $connectionCheck ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }
}
