<?php

declare(strict_types=1);

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
use Webmozart\Assert\Assert;

class CreateIdentifierGeneratorController
{
    public function __construct(
        private CreateGeneratorHandler $createGeneratorHandler,
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $content = $this->getContent($request);
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
        Assert::notNull($identifierGenerator);

        return new JsonResponse($identifierGenerator->normalizeForFront(), Response::HTTP_CREATED);
    }

    /**
     * @return array{code: string}
     */
    private function getContent(Request $request): array
    {
        $content = json_decode($request->getContent(), true);
        if (null === $content) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        Assert::isArray($content);
        Assert::keyExists($content, 'code');

        return $content;
    }
}
