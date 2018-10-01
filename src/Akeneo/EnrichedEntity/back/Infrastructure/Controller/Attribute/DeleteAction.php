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

    /** @var DeleteAttributeHandler */
    private $deleteAttributeHandler;

    public function __construct(
        DeleteAttributeHandler $deleteAttributeHandler,
        SecurityFacade $securityFacade
    ) {
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
        $command->attributeIdentifier = $attributeIdentifier;

        try {
            ($this->deleteAttributeHandler)($command);
        } catch (AttributeNotFoundException $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
