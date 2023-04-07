<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal\AssetManager;

use Akeneo\Catalogs\Application\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\AssetManager\FindOneAssetAttributeByIdentifierQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAssetAttributeAction
{
    public function __construct(
        private readonly FindOneAssetAttributeByIdentifierQueryInterface $findOneAssetAttributeByIdentifierQuery,
    ) {
    }

    public function __invoke(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        // todo : get PIM current locale
        $assetAttribute = $this->findOneAssetAttributeByIdentifierQuery->execute($identifier, 'en_US');

        if (null === $assetAttribute) {
            throw new NotFoundHttpException(\sprintf('Asset attribute "%s" does not exist.', $identifier));
        }

        return new JsonResponse($assetAttribute);
    }
}
