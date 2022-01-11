<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Controller\Asset;

use Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes\SearchLinkedProductAttributes;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Get Product Attributes by asset family identifier
 *
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class SearchLinkedProductAttributesAction
{
    public function __construct(
        private SearchLinkedProductAttributes $searchLinkedProductAttributes
    ) {
    }

    public function __invoke(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if ($request->query->get('assetFamilyIdentifier') === null) {
            return new JsonResponse('The asset family identifier is missing', Response::HTTP_BAD_REQUEST);
        }

        $familyIdentifier = AssetFamilyIdentifier::fromString($request->query->get('assetFamilyIdentifier'));

        $productAttributes = $this->searchLinkedProductAttributes->searchByFamilyIdentifier($familyIdentifier);
    }
}
