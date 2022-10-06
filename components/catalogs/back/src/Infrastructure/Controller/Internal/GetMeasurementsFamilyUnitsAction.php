<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetMeasurementsFamilyUnitsAction
{
    public function __construct(
        private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $locale = $request->query->get('locale', 'en_US');

        if (!\is_string($locale)) {
            throw new BadRequestHttpException('Locale must be a string.');
        }

        $measurementFamily = $this->getMeasurementsFamilyQuery->execute($code, $locale);

        if (null === $measurementFamily) {
            throw new NotFoundHttpException(\sprintf('measurements family "%s" does not exist.', $code));
        }

        return new JsonResponse($measurementFamily['units']);
    }
}
