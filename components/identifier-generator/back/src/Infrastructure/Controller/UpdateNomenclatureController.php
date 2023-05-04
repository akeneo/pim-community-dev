<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnexpectedAttributeTypeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateNomenclatureController
{
    public function __construct(
        private readonly UpdateNomenclatureHandler $updateNomenclatureHandler,
    ) {
    }

    public function __invoke(Request $request, string $propertyCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $content = $this->getContent($request);

        $command = new UpdateNomenclatureCommand(
            propertyCode: $propertyCode,
            operator: $content['operator'] ?? null,
            value: $content['value'] ?? null,
            generateIfEmpty: $content['generate_if_empty'] ?? null,
            values: $content['values'] ?? [],
        );

        try {
            ($this->updateNomenclatureHandler)($command);
        } catch (ViolationsException $exception) {
            return new JsonResponse($exception->normalize(), Response::HTTP_BAD_REQUEST);
        } catch (UndefinedAttributeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (UnexpectedAttributeTypeException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse();
    }

    /**
     * @return array{
     *     operator?: string,
     *     value?: int,
     *     generate_if_empty?: bool,
     *     values?: array<string, ?string>,
     * }
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
