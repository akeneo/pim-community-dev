<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Builder\TemplateBuilder;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        private TemplateBuilder $templateBuilder
    ) {
    }

    public function __invoke(Request $request, string $templateCode, int $categoryTreeId): JsonResponse
    {
        if ($this->securityFacade->isGranted('pim_enrich_product_category_template') === false) {
            throw new AccessDeniedException();
        }

        $data = $this->getDecodedContent($request->getContent());
        $templateCode = $data['code'];
        $templateLabelCollection = LabelCollection::fromArray($data['labels']);

        $categoryTree = $this->getCategory->byId($categoryTreeId);

        //todo: query to check if the category tree has already a template
        $template = $this->templateBuilder->generateTemplate($categoryTree->getCode(), $templateCode, $templateLabelCollection);

        return new JsonResponse($template->normalize(), Response::HTTP_OK);
    }

    private function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }
}
