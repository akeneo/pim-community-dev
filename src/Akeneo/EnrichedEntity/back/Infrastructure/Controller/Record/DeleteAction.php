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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Record;

use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordHandler;
use Akeneo\EnrichedEntity\Domain\Repository\RecordNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Delete a record
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAction
{
    /** @var DeleteRecordHandler */
    private $deleteRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        DeleteRecordHandler $deleteRecordHandler,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->deleteRecordHandler = $deleteRecordHandler;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->normalizer = $normalizer;
    }

    public function __invoke(Request $request, string $enrichedEntityIdentifier, string $recordCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->securityFacade->isGranted('akeneo_enrichedentity_record_delete')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteRecordCommand();
        $command->recordCode = $recordCode;
        $command->enrichedEntityIdentifier = $enrichedEntityIdentifier;

        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            ($this->deleteRecordHandler)($command);
        } catch (RecordNotFoundException | \InvalidArgumentException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
