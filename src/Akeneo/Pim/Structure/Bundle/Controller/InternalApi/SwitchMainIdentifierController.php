<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierCommand;
use Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier\SwitchMainIdentifierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchMainIdentifierController
{
    public function __construct(
        private readonly SwitchMainIdentifierHandler $switchMainIdentifierHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $newMainIdentifierCode = $request->get('attribute_code');
        try {
            Assert::stringNotEmpty($newMainIdentifierCode);
        } catch (\InvalidArgumentException) {
            throw new BadRequestHttpException('attribute_code must be a non empty string');
        }

        $command = SwitchMainIdentifierCommand::fromIdentifierCode($newMainIdentifierCode);
        ($this->switchMainIdentifierHandler)($command);

        return new JsonResponse(['result' => 'ok']);
    }
}
