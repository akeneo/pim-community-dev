<?php

namespace Pim\Bundle\NotificationBundle\Controller;

use Pim\Bundle\NotificationBundle\Update\UpdateUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * New version controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateController
{
    /** @var UpdateUrlGeneratorInterface */
    protected $urlGenerator;

    /**
     * @param UpdateUrlGeneratorInterface $urlGenerator
     */
    public function __construct(UpdateUrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Provides url to call to fetch new versions
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function generateUrlAction(Request $request)
    {
        $response = ['update_url' => $this->urlGenerator->generateAvailablePatchsUrl()];

        return new JsonResponse($response);
    }
}
