<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Exception\InvalidProductSelectionCriteriaException;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\CountProductsSelectedByCriteriaQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProductSelectionCriteria from Catalog
 */
final class CountProductsSelectedByCriteriaAction
{
    public function __construct(
        private CountProductsSelectedByCriteriaQueryInterface $getNumberOfProductsSelectedQuery,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productSelectionCriteria = \json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($productSelectionCriteria)) {
            throw new BadRequestHttpException('The product selection criteria must be an array.');
        }

        try {
            /** @var ProductSelectionCriteria $productSelectionCriteria */
            $count = $this->getNumberOfProductsSelectedQuery->execute($productSelectionCriteria);
        } catch (InvalidProductSelectionCriteriaException) {
            throw new BadRequestHttpException('Given product selection criteria are invalid.');
        }

        return new JsonResponse($count);
    }
}
