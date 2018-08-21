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

namespace Akeneo\EnrichedEntity\Infrastructure\Controller\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute\DeleteAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteAction
{
    /** @var SecurityFacade */
    private $securityFacade;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ValidatorInterface */
    private $validator;

    /** @var DeleteAttributeHandler */
    private $deleteAttributeHandler;

    public function __construct(
        DeleteAttributeHandler $deleteAttributeHandler,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade
    ) {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->deleteAttributeHandler = $deleteAttributeHandler;
    }

    public function __invoke(Request $request, string $enrichedEntityIdentifier, string $attributeIdentifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->securityFacade->isGranted('akeneo_enrichedentity_attribute_delete')) {
            throw new AccessDeniedException();
        }

        $command = new DeleteAttributeCommand();
        $command->identifier = [
            'identifier' => $attributeIdentifier,
            'enrichedEntityIdentifier' => $enrichedEntityIdentifier,
        ];

        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            return new JsonResponse(
                $this->normalizer->normalize($violations, 'internal_api'),
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            ($this->deleteAttributeHandler)($command);
        } catch (AttributeNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
