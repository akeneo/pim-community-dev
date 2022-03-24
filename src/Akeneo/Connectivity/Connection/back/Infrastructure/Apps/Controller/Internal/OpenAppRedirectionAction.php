<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OpenAppRedirectionAction
{
    public function __construct(
        private FeatureFlag $marketplaceActivateFeatureFlag,
        private SecurityFacade $security,
        private FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
    ) {
    }

    public function __invoke(string $connectionCode)
    {
        $connectedApp = $this->findOneConnectedAppByConnectionCodeQuery->execute($connectionCode);

        if (null === $connectedApp) {
            throw new NotFoundHttpException("Connected app with connection code $connectionCode does not exist.");
        }

        return new RedirectResponse('akeneo.com');
    }
}
