<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Mapping\TargetTypeConverter;
use Akeneo\Catalogs\Application\Persistence\Attribute\SearchAttributesQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributesByTargetTypeAndTargetFormatAction
{
    public function __construct(
        private SearchAttributesQueryInterface $searchAttributesQuery,
        private TargetTypeConverter $targetTypeConverter,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $search = $request->query->get('search', null);
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $targetType = $request->query->get('targetType', null);
        $targetFormat = $request->query->get('targetFormat', '');

        if ($page < 1 || $limit < 1) {
            throw new BadRequestHttpException('Page and limit must be positive.');
        }
        if (!\is_string($search) && null !== $search) {
            throw new BadRequestHttpException('Search must be a string or null.');
        }
        if (!\is_string($targetType) && null !== $targetType) {
            throw new BadRequestHttpException('TargetType must be a string or null.');
        }
        if (!\is_string($targetFormat) && null !== $targetFormat) {
            throw new BadRequestHttpException('TargetFormat must be a string or null.');
        }
        if (empty($targetType)) {
            throw new BadRequestHttpException('TargetType must be filled.');
        }

        $attributeTypes = [];

        try {
            $attributeTypes = $this->targetTypeConverter->toAttributeTypes($targetType, $targetFormat ?? '');
        } catch (NoCompatibleAttributeTypeFoundException $exception) {
            throw new BadRequestHttpException(
                \sprintf(
                    'The combination of type "%s" and format "%s" does not match any type in the PIM.',
                    $targetType,
                    $targetFormat ?? '',
                ),
                $exception,
            );
        }

        $attributes = $this->searchAttributesQuery->execute($search, $page, $limit, $attributeTypes);

        return new JsonResponse($attributes);
    }
}
