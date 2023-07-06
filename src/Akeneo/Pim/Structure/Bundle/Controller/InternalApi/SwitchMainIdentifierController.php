<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierCommand;
use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierHandler;
use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierValidator;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchMainIdentifierController
{
    public function __construct(
        private readonly SwitchMainIdentifierHandler $switchMainIdentifierHandler,
        private readonly SwitchMainIdentifierValidator $switchMainIdentifierValidator,
        private readonly SecurityFacade $security,
    ) {
    }

    public function __invoke(Request $request, string $attributeCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->security->isGranted('pim_enrich_attribute_edit')) {
            throw new AccessDeniedException();
        }

        try {
            Assert::stringNotEmpty($attributeCode);
        } catch (\InvalidArgumentException) {
            throw new BadRequestHttpException('attribute_code must be a non empty string');
        }

        $command = SwitchMainIdentifierCommand::fromIdentifierCode($attributeCode);
        try {
            $this->switchMainIdentifierValidator->validate($command);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse($e->getMessage(), 400);
        }
        ($this->switchMainIdentifierHandler)($command);

        return new JsonResponse(['result' => 'ok']);
    }
}
