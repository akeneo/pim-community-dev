<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetGeneratorQuery;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetGeneratorHandler;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateIdentifierGeneratorController
{
    public function __construct(
        private readonly CreateGeneratorHandler $createGeneratorHandler,
        private readonly GetGeneratorHandler $getGeneratorHandler,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_manage')) {
            throw new AccessDeniedException();
        }

        $content = $this->getContent($request);
        $command = new CreateGeneratorCommand(
            $content['code'],
            $content['conditions'],
            $content['structure'],
            $content['labels'],
            $content['target'],
            $content['delimiter'],
            $content['text_transformation']
        );

        try {
            ($this->createGeneratorHandler)($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ($this->getGeneratorHandler)(GetGeneratorQuery::fromCode($content['code'])),
            Response::HTTP_CREATED
        );
    }

    /**
     * @return array{
     *     code: string,
     *     conditions: list<array<string, mixed>>,
     *     structure: list<array<string, mixed>>,
     *     labels: array<string, string>,
     *     target: string,
     *     delimiter: ?string,
     *     text_transformation: string
     * }
     */
    private function getContent(Request $request): array
    {
        $data = \json_decode($request->getContent(), true);
        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return [
            'code' => \is_string($data['code'] ?? null) ? $data['code'] : '',
            'conditions' => \is_array($data['conditions'] ?? null) ? $data['conditions'] : [],
            'structure' => \is_array($data['structure'] ?? null) ? $data['structure'] : [],
            'labels' => \is_array($data['labels'] ?? null) ? $data['labels'] : [],
            'target' => \is_string($data['target'] ?? null) ? $data['target'] : '',
            'delimiter' => \is_string($data['delimiter'] ?? null) ? $data['delimiter'] : null,
            'text_transformation' => \is_string($data['text_transformation'] ?? null) ? $data['text_transformation'] : '',
        ];
    }
}
