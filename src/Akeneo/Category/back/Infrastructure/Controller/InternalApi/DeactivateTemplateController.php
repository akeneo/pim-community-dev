<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Api\Command\CommandMessageBus;
use Akeneo\Category\Application\Command\DeactivateTemplateCommand;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly GetTemplate $getTemplate,
        private readonly CommandMessageBus $categoryCommandBus,
    ) {
    }

    public function __invoke(Request $request, string $uuid): Response
    {
        if (!$this->securityFacade->isGranted('pim_enrich_product_category_template')
        ) {
            throw new AccessDeniedException();
        }

        $template = $this->getTemplate->byUuid(TemplateUuid::fromString($uuid));

        if ($template === null) {
            throw new NotFoundHttpException('Template not found');
        }

        try {
            $command = DeactivateTemplateCommand::create($uuid);
            $this->categoryCommandBus->dispatch($command);
        } catch (\Exception $e) {
            throw new \RuntimeException();
        }
    }
}
