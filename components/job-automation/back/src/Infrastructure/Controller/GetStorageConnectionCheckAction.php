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

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\StorageValidator;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Platform\JobAutomation\Application\StorageConnectionCheck\StorageConnectionCheckHandler;
use Akeneo\Platform\JobAutomation\Application\StorageConnectionCheck\StorageConnectionCheckQuery;
use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Sftp\SftpStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetStorageConnectionCheckAction
{
    public function __construct(
        private StorageConnectionCheckHandler $storageConnectionCheckHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new Storage(['xlsx', 'xls']));
        if (0 < $violations->count()) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $this->storageConnectionCheckHandler->handle(new StorageConnectionCheckQuery(
            $request->getContent()
        ));
    }
}
