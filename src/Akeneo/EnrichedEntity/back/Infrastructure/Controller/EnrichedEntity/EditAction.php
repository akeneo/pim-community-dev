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

namespace Akeneo\EnrichedEntity\back\Infrastructure\Controller\EnrichedEntity;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity\RawDataValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Edit enriched entity action
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditAction
{
    /** @var EditEnrichedEntityHandler */
    private $editEnrichedEntityHandler;

    /** @var NormalizerInterface */
    private $enrichedEntityNormalizer;

    /**
     * @param EditEnrichedEntityHandler $editEnrichedEntityHandler
     * @param NormalizerInterface       $enrichedEntityNormalizer
     */
    public function __construct(
        EditEnrichedEntityHandler $editEnrichedEntityHandler,
        NormalizerInterface $enrichedEntityNormalizer
    ) {
        $this->editEnrichedEntityHandler = $editEnrichedEntityHandler;
        $this->enrichedEntityNormalizer  = $enrichedEntityNormalizer;
    }

    /**
     * Save an enriched entity
     *
     * @return Response
     */
    public function saveAction(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $identifier = $request->get('identifier');
        $data = $request->getContent();

        $validator = new RawDataValidator();
        $violations = $validator->validate($data);

        if ($violations->count() > 0) {
            return new JsonResponse(['errors' => json_encode($violations)], Response::HTTP_BAD_REQUEST);
        }

        $handler = $this->editEnrichedEntityHandler;
        $enrichedEntity = $handler($identifier, $data['labels']);

        return new JsonResponse(
            $this->enrichedEntityNormalizer->normalize($enrichedEntity, 'internal_api')
        );
    }
}
