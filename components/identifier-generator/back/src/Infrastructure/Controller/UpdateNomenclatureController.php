<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
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
            propertyType: 'family',
            operator: $content['operator'],
            value: $content['value'],
            values: $content['families'],
        );
        ($this->updateNomenclatureHandler)($command);

        return new JsonResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function getContent(Request $request): array
    {
        $content = json_decode($request->getContent(), true);
        if (null === $content) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        Assert::isArray($content);

        return $content;
    }
}
