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

namespace PimEnterprise\Bundle\WorkflowBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\Workflow\Query\DraftAuthors;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthorDraftController
{
    /** @var DraftAuthors */
    private $draftAuthors;

    /** @var UserContext */
    private $userContext;

    public function __construct(DraftAuthors $draftAuthors, UserContext $userContext)
    {
        $this->draftAuthors = $draftAuthors;
        $this->userContext = $userContext;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function authorAction(Request $request)
    {
        $options = $request->query->get('options');

        $page = isset($options['page']) ? (int) $options['page'] : 0;
        $limit = isset($options['limit']) ? (int) $options['limit'] : SearchableRepositoryInterface::FETCH_LIMIT;

        $identifiers = [];
        if ($request->query->has('identifiers')) {
            $identifiers = explode(',', $request->query->get('identifiers'));
        }

        $authors = $this->draftAuthors->findAuthors($request->query->get('search'), $page, $limit, $identifiers);

        $normalized = [];
        foreach ($authors as $author) {
            $normalized[$author['username']] = [
                'username' => $author['username'],
                'code' => $author['username'],
                'labels' => [
                    $this->userContext->getUiLocaleCode() => $author['username']
                ]
            ];
        }

        return new JsonResponse($normalized);
    }
}
