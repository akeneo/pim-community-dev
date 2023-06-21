<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Domain\Exception\TemplateNotFoundException;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2024 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TrackUsageOfLoadPredefinedAttributesController
{
    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private GetTemplate $getTemplate,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, string $templateUuid): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_template') === false) {
            throw new AccessDeniedException();
        }

        try {
            $this->getTemplate->byUuid(TemplateUuid::fromString($templateUuid));
        } catch (TemplateNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $data = json_decode($request->getContent(), true);
        $action = $data['action'] ?? '';
        $context = [
            'log_type' => 'create_category_template_tracking',
            'message' => 'Category template created with[out] set of attributes',
            'level' => 'notice',
            'action' => "$action",
            'template_uuid' => "$templateUuid",
        ];

        $this->logger->notice($templateUuid, $context);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
