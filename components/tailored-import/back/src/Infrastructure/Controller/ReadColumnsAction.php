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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Application\ReadColumns\ReadColumnsHandler;
use Akeneo\Platform\TailoredImport\Application\ReadColumns\ReadColumnsQuery;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\ReadColumns;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReadColumnsAction
{
    public function __construct(
        private ReadColumnsHandler $readColumnsHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $fileKey = $request->get('file_key');
        $normalizedFileStructure = $request->get('file_structure');

        if (null === $fileKey) {
            throw new BadRequestException('Missing file key');
        }

        $violations = $this->validator->validate($request, new ReadColumns());
        if (count($violations) > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $fileStructure = FileStructure::createFromNormalized($normalizedFileStructure);

        $readColumnsQuery = new ReadColumnsQuery($fileKey, $fileStructure);

        $columns = $this->readColumnsHandler->handle($readColumnsQuery);
        $normalizedColumns = $columns->normalize();

        return new JsonResponse($normalizedColumns);
    }
}
