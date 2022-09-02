<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllTestAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllTestAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllTestAppsAction
{
    public function __construct(
        private FeatureFlag $appDeveloperMode,
        private AppUrlGenerator $appUrlGenerator,
        private GetAllTestAppsQueryInterface $getAllTestAppsQuery
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->appDeveloperMode->isEnabled()) {
            return new JsonResponse(GetAllTestAppsResult::create(0, [])->normalize());
        }

        $result = $this->getAllTestAppsQuery->execute();
        $resultWithPimUrl = $result->withPimUrlSource($this->appUrlGenerator->getAppQueryParameters());

        return new JsonResponse($resultWithPimUrl->normalize());
    }
}
