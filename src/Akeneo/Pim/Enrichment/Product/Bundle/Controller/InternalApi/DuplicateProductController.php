<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductWithoutUniqueValues;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct\DuplicateProductWithoutUniqueValuesHandler;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DuplicateProductController
{
    /** @var DuplicateProductWithoutUniqueValuesHandler */
    private $duplicateProductWithoutUniqueValuesHandler;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        DuplicateProductWithoutUniqueValuesHandler $duplicateProductWithoutUniqueValuesHandler,
        SaverInterface $productSaver
    ) {
        $this->duplicateProductWithoutUniqueValuesHandler = $duplicateProductWithoutUniqueValuesHandler;
        $this->productSaver = $productSaver;
    }

    public function duplicateProductAction(Request $request, $id)
    {
        if (!$request->request->has('identifier')) {
            throw new UnprocessableEntityHttpException('You should give either an "identifier" key.');
        }

        $query = new DuplicateProductWithoutUniqueValues($id, $request->request->get('identifier'));

        list($duplicatedProduct) = $this->duplicateProductWithoutUniqueValuesHandler->handle($query);

        $this->productSaver->save($duplicatedProduct);

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
