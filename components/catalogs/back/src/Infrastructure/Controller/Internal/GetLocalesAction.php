<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetLocalesAction
{
    public function __construct(
        private GetLocalesQueryInterface $getLocalesQuery,
        private GetLocalesByCodeQueryInterface $getLocalesByCodeQuery,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $concatCodes = $request->query->get('codes', null);

        if (null !== $concatCodes && !\is_string($concatCodes)) {
            throw new BadRequestHttpException('Codes must be a string concatenated with comma or null.');
        }

        if ($page < 1 || $limit < 1) {
            throw new BadRequestHttpException('Page and limit must be positive.');
        }

        $locales = $this->getLocales($concatCodes, $page, $limit);

        return new JsonResponse($locales);
    }

    /**
     * @return array<array-key, array{code: string, label: string}>
     */
    private function getLocales(?string $concatCodes, int $page, int $limit): array
    {
        if (null === $concatCodes) {
            return $this->getLocalesQuery->execute($page, $limit);
        }

        $codes = $concatCodes !== '' ? \explode(',', $concatCodes) : [];

        return $this->getLocalesByCodeQuery->execute($codes, $page, $limit);
    }
}
