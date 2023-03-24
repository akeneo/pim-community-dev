<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateIdentifierGeneratorController
{
    public function __construct(
        private readonly UpdateGeneratorHandler $updateGeneratorHandler,
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
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

        try {
            $content = $this->getContent($request);
            $command = UpdateGeneratorCommand::fromNormalized($code, $content);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([['message' => $exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->updateGeneratorHandler)($command);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        }

        $identifierGeneratorUpdated = $this->identifierGeneratorRepository->get($code);
        Assert::notNull($identifierGeneratorUpdated);

        return new JsonResponse($identifierGeneratorUpdated->normalize(), Response::HTTP_OK);
    }

    /**
     * @return array<string, mixed>
     */
    private function getContent(Request $request): array
    {
        $content = \json_decode($request->getContent(), true);
        if (null === $content) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        Assert::isArray($content);

        return $content;
    }
}
