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

namespace Akeneo\ReferenceEntity\Infrastructure\Controller\Record;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fetches the binary preview of the url
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ImagePreviewAction
{
    public function __invoke(
        Request $request,
        string $data,
        string $attributeIdentifier,
        string $type
    ): Response {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
