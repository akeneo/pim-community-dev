<?php

namespace Akeneo\Platform\Job\Infrastructure\Controller;

use Akeneo\Platform\Job\Domain\Query\FindJobTypesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetJobTypesAction
{
    private FindJobTypesInterface $findJobTypes;

    public function __construct(FindJobTypesInterface $findJobTypes)
    {
        $this->findJobTypes = $findJobTypes;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $jobTypes = $this->findJobTypes->visible();

        return new JsonResponse($jobTypes);
    }
}
