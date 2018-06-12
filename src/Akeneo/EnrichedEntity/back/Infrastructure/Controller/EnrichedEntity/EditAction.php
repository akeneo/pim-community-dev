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

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EditEnrichedEntity\EditEnrichedEntityHandler;
use Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity\EnrichedEntityEditCommandValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var Serializer */
    private $serializer;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param EditEnrichedEntityHandler $editEnrichedEntityHandler
     * @param NormalizerInterface       $enrichedEntityNormalizer
     * @param Serializer                $serializer
     * @param ValidatorInterface        $validator
     */
    public function __construct(
        EditEnrichedEntityHandler $editEnrichedEntityHandler,
        NormalizerInterface $enrichedEntityNormalizer,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        $this->editEnrichedEntityHandler = $editEnrichedEntityHandler;
        $this->enrichedEntityNormalizer  = $enrichedEntityNormalizer;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Save an enriched entity
     *
     * @return Response
     */
    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $command = $this->serializer->deserialize($request->getContent(), EditEnrichedEntityCommand::class, 'json');
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                // TODO: format the error the way we want for the front
                $errors[] = $violation->getPropertyPath() .' '. $violation->getMessage();
            }

            return new JsonResponse(['errors' => json_encode($errors)], Response::HTTP_BAD_REQUEST);
        }

        $enrichedEntity = ($this->editEnrichedEntityHandler)($command);

        return new JsonResponse(
            $this->enrichedEntityNormalizer->normalize($enrichedEntity, 'internal_api')
        );
    }
}
