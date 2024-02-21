<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetGeneratorQuery;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorHandler;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateIdentifierGeneratorController
{
    public function __construct(
        private readonly UpdateGeneratorHandler $updateGeneratorHandler,
        private readonly GetGeneratorHandler $getGeneratorHandler,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_manage')) {
            throw new AccessDeniedException();
        }

        $content = $this->getContent($request);
        $command = new UpdateGeneratorCommand(
            $code,
            $content['conditions'],
            $content['structure'],
            $content['labels'],
            $content['target'],
            $content['delimiter'],
            $content['text_transformation']
        );

        try {
            ($this->updateGeneratorHandler)($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ($this->getGeneratorHandler)(GetGeneratorQuery::fromCode($code)),
            Response::HTTP_OK
        );
    }

    /**
     * @return array{
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
            'conditions' => \is_array($data['conditions'] ?? null) ? $data['conditions'] : [],
            'structure' => \is_array($data['structure'] ?? null) ? $data['structure'] : [],
            'labels' => \is_array($data['labels'] ?? null) ? $data['labels'] : [],
            'target' => \is_string($data['target'] ?? null) ? $data['target'] : '',
            'delimiter' => \is_string($data['delimiter'] ?? null) ? $data['delimiter'] : null,
            'text_transformation' => \is_string($data['text_transformation'] ?? null) ? $data['text_transformation'] : '',
        ];
    }
}
