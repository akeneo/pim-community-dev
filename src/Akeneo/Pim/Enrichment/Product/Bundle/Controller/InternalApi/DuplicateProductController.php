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

use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProductWithoutUniqueValues;
use Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProductWithoutUniqueValuesHandler;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Duplicate Product Controller
 *
 * @author    Christophe Chausseray
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateProductController
{
    /** @var DuplicateProductWithoutUniqueValuesHandler */
    private $duplicateProductWithoutUniqueValues;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        DuplicateProductWithoutUniqueValuesHandler $duplicateProductWithoutUniqueValues,
        SaverInterface $productSaver
    ) {
        $this->duplicateProductWithoutUniqueValues = $duplicateProductWithoutUniqueValues;
        $this->productSaver = $productSaver;
    }

    public function duplicateProductAction(Request $request, $id)
    {
        if (!$request->request->has('identifier')) {
            throw new UnprocessableEntityHttpException('You should give either an "identifier" key.');
        }

        $query = new DuplicateProductWithoutUniqueValues($id, $request->request->get('identifier'));

        list($duplicatedProduct) = $this->duplicateProductWithoutUniqueValues->handle($query);

        $this->productSaver->save($duplicatedProduct);

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
