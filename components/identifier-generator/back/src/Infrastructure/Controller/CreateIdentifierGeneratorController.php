<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateIdentifierGeneratorController
{
    public function __construct(
        private CreateGeneratorHandler $createGeneratorHandler,
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    public function __invoke(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $content = $this->getContent($request);

        try {
            $command = CreateGeneratorCommand::fromNormalized($content);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([['message' => $exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->createGeneratorHandler)($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        }

        $identifierGenerator = $this->identifierGeneratorRepository->get($content['code']);

        return new JsonResponse($identifierGenerator->normalize(), Response::HTTP_CREATED);
    }

    private function getContent(Request $request): array
    {
        $content = json_decode($request->getContent(), true);
        if (null === $content) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $content;
    }
}
