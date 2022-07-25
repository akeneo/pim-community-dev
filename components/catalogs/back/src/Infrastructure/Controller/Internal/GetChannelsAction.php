<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetChannelsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetChannelsAction
{
    public function __construct(private GetChannelsQueryInterface $getChannelsQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $code = $request->query->get('code');

        if ($page < 1 || $limit < 1) {
            throw new BadRequestHttpException('Page and limit must be positive.');
        }

        if (null !== $code && !\is_string($code)) {
            throw new BadRequestHttpException('Code must be a string or null.');
        }

        $channels = $this->getChannelsQuery->execute($page, $limit, $code);

        return new JsonResponse($channels);
    }
}
