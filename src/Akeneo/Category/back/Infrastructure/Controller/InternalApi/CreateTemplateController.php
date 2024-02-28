<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateTemplateController
{
    public function __construct(
        private SecurityFacade $securityFacade,
        private GetCategoryInterface $getCategory,
        private ActivateTemplate $activateTemplate,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Request $request, int $categoryTreeId): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_template') === false) {
            throw new AccessDeniedException();
        }

        $data = $request->toArray();
        $templateCode = new TemplateCode($data['code']);
        $templateLabelCollection = LabelCollection::fromArray($data['labels']);

        $categoryTree = $this->getCategory->byId($categoryTreeId);
        $templateUuid = ($this->activateTemplate)($categoryTree->getId(), $templateCode, $templateLabelCollection);

        return new JsonResponse(['uuid' => (string) $templateUuid], Response::HTTP_OK);
    }
}
