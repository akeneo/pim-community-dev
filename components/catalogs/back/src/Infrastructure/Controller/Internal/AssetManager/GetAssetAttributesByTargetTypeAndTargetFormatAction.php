<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal\AssetManager;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Mapping\TargetTypeConverter;
use Akeneo\Catalogs\Application\Persistence\AssetManager\SearchAssetAttributesQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAssetAttributesByTargetTypeAndTargetFormatAction
{
    public function __construct(
        private SearchAssetAttributesQueryInterface $searchAssetAttributesQuery,
        private TargetTypeConverter $targetTypeConverter,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $search = $request->query->get('search', null);
        $assetFamily = $request->query->get('assetFamily', null);
        $targetType = $request->query->get('targetType', null);
        $targetFormat = $request->query->get('targetFormat', '');

        if (!\is_string($assetFamily)) {
            throw new BadRequestHttpException('AssetFamily must be a string.');
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

        $assetAttributes = $this->searchAssetAttributesQuery->execute($assetFamily, $search, $attributeTypes);

        return new JsonResponse($assetAttributes);
    }
}
